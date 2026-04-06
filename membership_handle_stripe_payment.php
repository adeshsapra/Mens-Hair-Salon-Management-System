<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/stripe_config.php';
require_once __DIR__ . '/payment_integration_helpers.php';
require_once __DIR__ . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function membershipRedirectWithError(string $message): void
{
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = $message;
    header('Location: membership.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: membership.php');
    exit();
}

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    header('Location: login.php');
    exit();
}

if (!STRIPE_ENABLED || STRIPE_SECRET_KEY === '' || STRIPE_PUBLISHABLE_KEY === '') {
    membershipRedirectWithError('Stripe account is not connected.');
}

$paymentIntentId = trim((string) ($_POST['payment_intent_id'] ?? ''));
$passKey = trim((string) ($_POST['pass_key'] ?? ''));
$billingPlan = trim((string) ($_POST['billing_plan'] ?? ''));
$cardName = trim((string) ($_POST['card_name'] ?? ''));
$phoneNumber = preg_replace('/\D+/', '', (string) ($_POST['phone_number'] ?? ''));

if ($paymentIntentId === '' || $cardName === '' || strlen($phoneNumber) !== 10) {
    membershipRedirectWithError('Please complete valid payment details.');
}

if (!in_array($passKey, ['royal', 'classic', 'standard'], true) || !in_array($billingPlan, ['yearly', 'monthly'], true)) {
    membershipRedirectWithError('Invalid membership plan selected.');
}

$planStmt = mysqli_prepare(
    $con,
    'SELECT mp_id, display_name, price FROM membership_plans WHERE pass_key = ? AND billing_plan = ? LIMIT 1'
);
if (!$planStmt) {
    membershipRedirectWithError('Unable to load membership plan.');
}
mysqli_stmt_bind_param($planStmt, 'ss', $passKey, $billingPlan);
mysqli_stmt_execute($planStmt);
$planResult = mysqli_stmt_get_result($planStmt);
$planRow = $planResult ? mysqli_fetch_assoc($planResult) : null;
mysqli_stmt_close($planStmt);

if (!$planRow) {
    membershipRedirectWithError('Membership plan not found.');
}

$membershipPlanId = (int) $planRow['mp_id'];
$price = (float) $planRow['price'];
$expectedAmount = (int) round($price * 100);
$membershipType = ucfirst($billingPlan) . ' ' . trim((string) $planRow['display_name']);

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
} catch (\Exception $e) {
    membershipRedirectWithError('Unable to verify Stripe payment.');
}

if (!$intent || $intent->status !== 'succeeded') {
    membershipRedirectWithError('Stripe payment is not successful.');
}

if ((int) $intent->amount !== $expectedAmount) {
    membershipRedirectWithError('Membership payment amount mismatch.');
}

// Prevent duplicate insert for the same Stripe intent.
$intentSafe = mysqli_real_escape_string($con, (string) $intent->id);
$dup = mysqli_query($con, "SELECT pay_id FROM payment WHERE stripe_payment_intent_id = '{$intentSafe}' LIMIT 1");
if ($dup && mysqli_num_rows($dup) > 0) {
    $_SESSION['toast-type'] = 'success';
    $_SESSION['toast-msg'] = 'Membership purchased successfully.';
    header('Location: thankyou_membership.php');
    exit();
}

$requiredColumns = ['m_id', 'payment_for', 'payment_note', 'p_amount'];
foreach ($requiredColumns as $columnName) {
    if (!paymentIntegrationColumnExists($con, 'payment', $columnName)) {
        membershipRedirectWithError('Payment table migration missing. Please run admin/setup_unified_payment_table.php once.');
    }
}
if (!paymentIntegrationMembershipTransactionsReady($con)) {
    membershipRedirectWithError('Membership transaction migration missing. Please run admin/setup_membership_transactions_table.php once.');
}

$nowDate = date('Y-m-d');
$nowTime = date('H:i:s');
$status = 'active';
$paymentMethod = 'stripe';
$intentStatus = (string) $intent->status;
$startDate = $nowDate;
$endDate = paymentIntegrationCalculateMembershipEndDate($startDate, $billingPlan);
$subscribedAt = $nowDate . ' ' . $nowTime;

$txStarted = mysqli_begin_transaction($con);
if (!$txStarted) {
    membershipRedirectWithError('Failed to start membership transaction save.');
}

$paymentInsertOk = false;
$membershipInsertOk = false;
$paymentId = 0;

$stmt = mysqli_prepare(
    $con,
    "INSERT INTO payment (
        id, s_id, m_id, payment_for, payment_note,
        p_name, p_phno, p_address, p_city, p_state, p_pincode,
        p_method, p_amount, p_date, p_time, p_status,
        stripe_payment_intent_id, stripe_payment_status
    ) VALUES (
        ?, NULL, ?, 'membership', ?,
        ?, ?, 'Membership', 'Membership', 'Membership', 0,
        ?, ?, ?, ?, ?,
        ?, ?
    )"
);

if ($stmt) {
    mysqli_stmt_bind_param(
        $stmt,
        'iissssdsssss',
        $user_id,
        $membershipPlanId,
        $membershipType,
        $cardName,
        $phoneNumber,
        $paymentMethod,
        $price,
        $nowDate,
        $nowTime,
        $status,
        $intentSafe,
        $intentStatus
    );
    $paymentInsertOk = mysqli_stmt_execute($stmt);
    if ($paymentInsertOk) {
        $paymentId = (int) mysqli_insert_id($con);
    }
    mysqli_stmt_close($stmt);
}

if ($paymentInsertOk && $paymentId > 0) {
    $membershipStmt = mysqli_prepare(
        $con,
        'INSERT INTO membership_transactions (
            pay_id, user_id, mp_id, membership_name, billing_plan, amount,
            start_date, end_date, status, subscribed_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if ($membershipStmt) {
        mysqli_stmt_bind_param(
            $membershipStmt,
            'iiissdssss',
            $paymentId,
            $user_id,
            $membershipPlanId,
            $membershipType,
            $billingPlan,
            $price,
            $startDate,
            $endDate,
            $status,
            $subscribedAt
        );
        $membershipInsertOk = mysqli_stmt_execute($membershipStmt);
        mysqli_stmt_close($membershipStmt);
    }
}

if (!$paymentInsertOk || !$membershipInsertOk) {
    mysqli_rollback($con);
    membershipRedirectWithError('Failed to complete membership purchase.');
}

mysqli_commit($con);

$_SESSION['toast-type'] = 'success';
$_SESSION['toast-msg'] = 'Membership purchased successfully.';
header('Location: thankyou_membership.php');
exit();
