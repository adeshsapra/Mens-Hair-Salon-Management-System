<?php
include 'connect.php';
require_once '../notification_helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$actorAdminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : 0;

if (!isset($_GET['pay_id'])) {
    header("Location: payment_manage.php?toast=error&msg=Missing+payment+ID.");
    exit();
}

$pay_id = (int) $_GET['pay_id'];
if ($pay_id <= 0) {
    header("Location: payment_manage.php?toast=error&msg=Invalid+payment+ID.");
    exit();
}

$paymentResult = mysqli_query(
    $con,
    "SELECT pay_id, id AS user_id, s_id, m_id, payment_for, p_status
     FROM payment
     WHERE pay_id = {$pay_id}
     LIMIT 1"
);
$paymentRow = $paymentResult ? mysqli_fetch_assoc($paymentResult) : null;

if (!$paymentRow) {
    header("Location: payment_manage.php?toast=error&msg=Payment+record+not+found.");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
    $new_status = 'Received';
    $msg = 'Payment confirmed successfully!';
} else {
    $new_status = 'Failed';
    $msg = 'Payment discarded successfully!';
}

$update_query = "UPDATE payment SET p_status = '$new_status' WHERE pay_id = $pay_id";

if (mysqli_query($con, $update_query)) {
    $userId = (int) ($paymentRow['user_id'] ?? 0);
    $paymentFor = strtolower(trim((string) ($paymentRow['payment_for'] ?? 'product')));
    $entityType = $paymentFor === 'membership' ? 'membership' : 'order';
    $entityId = $paymentFor === 'membership'
        ? (int) ($paymentRow['m_id'] ?? 0)
        : (int) ($paymentRow['s_id'] ?? 0);
    $userLink = $paymentFor === 'membership' ? 'user/membership_user.php' : 'user/order.php';
    $adminLink = $paymentFor === 'membership' ? 'admin/membership_details.php' : 'admin/manage_orders.php';

    if ($userId > 0) {
        notificationCreateForUser(
            $con,
            $userId,
            'payment_status_updated',
            'Payment Status Updated',
            "Payment #{$pay_id} status changed to {$new_status}.",
            $userLink,
            'admin',
            $actorAdminId,
            $entityType,
            $entityId
        );
    }

    notificationCreateForAllAdmins(
        $con,
        'payment_status_updated',
        'Payment Status Changed',
        "Payment #{$pay_id} status changed to {$new_status}.",
        $adminLink,
        'admin',
        $actorAdminId,
        $entityType,
        $entityId
    );

    header("Location: payment_manage.php?toast=success&msg=" . urlencode($msg));
    exit();
}

header("Location: payment_manage.php?toast=error&msg=" . urlencode('Error updating status: ' . mysqli_error($con)));
exit();

