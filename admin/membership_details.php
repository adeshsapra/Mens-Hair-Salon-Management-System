<?php
include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');
require_once __DIR__ . '/../payment_integration_helpers.php';

$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$tableReady = paymentIntegrationMembershipTransactionsReady($con);

if ($tableReady) {
    mysqli_query(
        $con,
        "UPDATE membership_transactions
         SET status = 'expired'
         WHERE status = 'active' AND end_date < CURDATE()"
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tableReady) {
    $action = strtolower(trim((string) ($_POST['membership_action'] ?? '')));
    if ($action === 'cancel') {
        $mtId = isset($_POST['mt_id']) ? (int) $_POST['mt_id'] : 0;
        if ($mtId <= 0) {
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Invalid membership transaction selected.';
            header('Location: membership_details.php');
            exit();
        }

        $lookupStmt = mysqli_prepare(
            $con,
            'SELECT mt_id, pay_id, status, end_date FROM membership_transactions WHERE mt_id = ? LIMIT 1'
        );
        if (!$lookupStmt) {
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Unable to cancel membership right now.';
            header('Location: membership_details.php');
            exit();
        }
        mysqli_stmt_bind_param($lookupStmt, 'i', $mtId);
        mysqli_stmt_execute($lookupStmt);
        $lookupResult = mysqli_stmt_get_result($lookupStmt);
        $currentRow = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;
        mysqli_stmt_close($lookupStmt);

        if (!$currentRow) {
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Membership transaction not found.';
            header('Location: membership_details.php');
            exit();
        }

        $normalizedStatus = paymentIntegrationNormalizeMembershipStatus(
            (string) ($currentRow['status'] ?? 'active'),
            (string) ($currentRow['end_date'] ?? '')
        );
        if ($normalizedStatus === 'cancelled') {
            $_SESSION['toast-type'] = 'info';
            $_SESSION['toast-msg'] = 'Membership is already cancelled.';
            header('Location: membership_details.php');
            exit();
        }

        $adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : 0;
        $payId = isset($currentRow['pay_id']) ? (int) $currentRow['pay_id'] : 0;

        $txStarted = mysqli_begin_transaction($con);
        if (!$txStarted) {
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Could not start cancellation transaction.';
            header('Location: membership_details.php');
            exit();
        }

        $cancelled = false;
        if ($adminId > 0) {
            $cancelStmt = mysqli_prepare(
                $con,
                "UPDATE membership_transactions
                 SET status = 'cancelled',
                     cancelled_at = NOW(),
                     cancelled_by_admin_id = ?,
                     updated_at = NOW()
                 WHERE mt_id = ?"
            );
            if ($cancelStmt) {
                mysqli_stmt_bind_param($cancelStmt, 'ii', $adminId, $mtId);
                $cancelled = mysqli_stmt_execute($cancelStmt);
                mysqli_stmt_close($cancelStmt);
            }
        } else {
            $cancelStmt = mysqli_prepare(
                $con,
                "UPDATE membership_transactions
                 SET status = 'cancelled',
                     cancelled_at = NOW(),
                     cancelled_by_admin_id = NULL,
                     updated_at = NOW()
                 WHERE mt_id = ?"
            );
            if ($cancelStmt) {
                mysqli_stmt_bind_param($cancelStmt, 'i', $mtId);
                $cancelled = mysqli_stmt_execute($cancelStmt);
                mysqli_stmt_close($cancelStmt);
            }
        }

        $paymentUpdated = true;
        if ($cancelled && $payId > 0) {
            $paymentStmt = mysqli_prepare(
                $con,
                "UPDATE payment
                 SET p_status = 'cancelled'
                 WHERE pay_id = ? AND (payment_for = 'membership' OR m_id IS NOT NULL)"
            );
            if ($paymentStmt) {
                mysqli_stmt_bind_param($paymentStmt, 'i', $payId);
                $paymentUpdated = mysqli_stmt_execute($paymentStmt);
                mysqli_stmt_close($paymentStmt);
            } else {
                $paymentUpdated = false;
            }
        }

        if ($cancelled && $paymentUpdated) {
            mysqli_commit($con);
            $_SESSION['toast-type'] = 'success';
            $_SESSION['toast-msg'] = 'Membership cancelled successfully.';
        } else {
            mysqli_rollback($con);
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Failed to cancel membership.';
        }

        header('Location: membership_details.php');
        exit();
    }
}

$total_records = 0;
$membership_data = false;
$offset = ($current_page - 1) * $records_per_page;

if ($tableReady) {
    $count_query = "SELECT COUNT(*) AS total FROM membership_transactions";
    $count_result = mysqli_query($con, $count_query);
    $count_row = $count_result ? mysqli_fetch_assoc($count_result) : ['total' => 0];
    $total_records = (int) ($count_row['total'] ?? 0);

    $membership_query = "
        SELECT
            mt.*,
            ur.name AS user_name,
            ur.username AS user_username,
            ur.email AS user_email,
            p.p_name AS card_holder_name,
            p.p_phno AS payment_phone,
            p.p_method AS payment_method,
            p.payment_note,
            p.stripe_payment_intent_id,
            mp.display_name AS plan_name,
            mp.features_json,
            a.admin_name AS cancelled_by_admin
        FROM membership_transactions mt
        LEFT JOIN user_reg ur ON mt.user_id = ur.id
        LEFT JOIN payment p ON mt.pay_id = p.pay_id
        LEFT JOIN membership_plans mp ON mt.mp_id = mp.mp_id
        LEFT JOIN admin a ON mt.cancelled_by_admin_id = a.admin_id
        ORDER BY mt.mt_id DESC
        LIMIT {$offset}, {$records_per_page}
    ";
    $membership_data = mysqli_query($con, $membership_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Transactions</title>
    <style>
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }
        .status-chip.active {
            background: #e7f9ef;
            color: #0f8a46;
        }
        .status-chip.expired {
            background: #fff4d8;
            color: #a46000;
        }
        .status-chip.cancelled {
            background: #fdeaea;
            color: #c53434;
        }
        .remaining-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            background: #f1f5f9;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }
        .table-container table th,
        .table-container table td {
            text-align: center;
            vertical-align: middle;
        }
        .action-dropdown-content form {
            margin: 0;
            display: block;
        }
        .membership-view-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 16px;
        }
        .membership-view-modal.show {
            display: flex;
        }
        .membership-view-card {
            background: #fff;
            width: min(860px, 100%);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        .membership-view-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .membership-view-header h3 {
            margin: 0;
            color: var(--bg1);
        }
        .membership-view-close {
            border: none;
            background: #f5f5f5;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            color: #555;
        }
        .membership-view-body {
            padding: 18px 20px 22px;
        }
        .membership-view-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .membership-view-block {
            background: #fafbfc;
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 14px;
        }
        .membership-view-block h4 {
            margin: 0 0 10px;
            color: var(--bg1);
            font-size: 15px;
        }
        .membership-info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 13px;
        }
        .membership-info-row:last-child {
            border-bottom: 0;
        }
        .membership-info-row strong {
            color: #111827;
        }
        .membership-features {
            margin: 8px 0 0;
            padding-left: 18px;
            font-size: 13px;
            color: #1f2937;
            display: grid;
            gap: 6px;
        }
        .membership-empty-state {
            padding: 20px;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            background: #fff;
            color: #374151;
        }
        @media (max-width: 860px) {
            .membership-view-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
renderAdminPageIntro(
    'Membership / Payment Details',
    'Membership Transactions',
    'Track each user subscription lifecycle with remaining days, status, and admin actions.'
);
?>
<div class="main-content">
    <div class="content">
        <h2>Membership Transaction Records</h2>

        <?php if (!$tableReady): ?>
            <div class="membership-empty-state">
                <strong>Membership transaction migration is pending.</strong><br>
                Run <code>admin/setup_membership_transactions_table.php</code> once, then refresh this page.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Membership ID</th>
                            <th>Membership Name</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th>Payment Ref</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $id_counter = $offset + 1;
                    if ($membership_data && mysqli_num_rows($membership_data) > 0) {
                        while ($row = mysqli_fetch_assoc($membership_data)) {
                            $status = paymentIntegrationNormalizeMembershipStatus(
                                (string) ($row['status'] ?? 'active'),
                                (string) ($row['end_date'] ?? '')
                            );
                            $remainingDays = paymentIntegrationMembershipRemainingDays(
                                (string) ($row['end_date'] ?? ''),
                                $status
                            );
                            $amount = (float) ($row['amount'] ?? 0);
                            $membershipName = trim((string) ($row['membership_name'] ?? ''));
                            if ($membershipName === '') {
                                $membershipName = trim((string) ($row['plan_name'] ?? 'Membership'));
                            }
                            $userName = trim((string) ($row['user_name'] ?? ''));
                            if ($userName === '') {
                                $userName = trim((string) ($row['user_username'] ?? 'User #' . (int) ($row['user_id'] ?? 0)));
                            }
                            $method = trim((string) ($row['payment_method'] ?? '-'));
                            $stripeRef = trim((string) ($row['stripe_payment_intent_id'] ?? ''));
                            $refLabel = $stripeRef !== '' ? $stripeRef : 'Offline/NA';
                            if ($stripeRef !== '' && strlen($stripeRef) > 20) {
                                $refLabel = substr($stripeRef, 0, 20) . '...';
                            }

                            $features = json_decode((string) ($row['features_json'] ?? '[]'), true);
                            if (!is_array($features)) {
                                $features = [];
                            }

                            $detailPayload = [
                                'transaction_id' => (int) ($row['mt_id'] ?? 0),
                                'payment_id' => isset($row['pay_id']) && $row['pay_id'] !== null ? (int) $row['pay_id'] : null,
                                'user_name' => $userName,
                                'user_email' => (string) ($row['user_email'] ?? ''),
                                'user_phone' => (string) ($row['payment_phone'] ?? ''),
                                'card_holder_name' => (string) ($row['card_holder_name'] ?? ''),
                                'membership_id' => isset($row['mp_id']) && $row['mp_id'] !== null ? (int) $row['mp_id'] : null,
                                'membership_name' => $membershipName,
                                'billing_plan' => ucfirst((string) ($row['billing_plan'] ?? '-')),
                                'amount' => number_format($amount, 2, '.', ''),
                                'start_date' => (string) ($row['start_date'] ?? '-'),
                                'end_date' => (string) ($row['end_date'] ?? '-'),
                                'remaining_days' => $remainingDays,
                                'status' => ucfirst($status),
                                'subscribed_at' => (string) ($row['subscribed_at'] ?? ''),
                                'cancelled_at' => (string) ($row['cancelled_at'] ?? ''),
                                'cancelled_by' => (string) ($row['cancelled_by_admin'] ?? ''),
                                'payment_method' => strtoupper($method),
                                'payment_note' => (string) ($row['payment_note'] ?? ''),
                                'stripe_intent' => (string) ($row['stripe_payment_intent_id'] ?? ''),
                                'features' => array_values($features),
                            ];
                            $detailJson = htmlspecialchars(
                                json_encode($detailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                    ?>
                        <tr>
                            <td><?php echo $id_counter++; ?></td>
                            <td><?php echo htmlspecialchars($userName); ?></td>
                            <td><?php echo !empty($row['mp_id']) ? (int) $row['mp_id'] : '-'; ?></td>
                            <td><?php echo htmlspecialchars($membershipName); ?></td>
                            <td>₹ <?php echo number_format($amount, 2); ?></td>
                            <td><?php echo htmlspecialchars((string) ($row['start_date'] ?? '-')); ?></td>
                            <td><?php echo htmlspecialchars((string) ($row['end_date'] ?? '-')); ?></td>
                            <td>
                                <span class="remaining-pill"><?php echo $remainingDays; ?> day(s)</span>
                            </td>
                            <td>
                                <span class="status-chip <?php echo htmlspecialchars($status); ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td style="font-size:11px;color:#374151;">
                                <?php echo htmlspecialchars(strtoupper($method)); ?><br>
                                <span title="<?php echo htmlspecialchars($stripeRef); ?>"><?php echo htmlspecialchars($refLabel); ?></span>
                            </td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="action-dots" onclick="toggleActionDropdown(event, <?php echo (int) ($row['mt_id'] ?? 0); ?>)" aria-label="Open actions" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="action-dropdown-content">
                                        <button type="button" onclick='openMembershipViewModal(<?php echo $detailJson; ?>)'>
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($status === 'active'): ?>
                                            <form method="post">
                                                <input type="hidden" name="membership_action" value="cancel">
                                                <input type="hidden" name="mt_id" value="<?php echo (int) ($row['mt_id'] ?? 0); ?>">
                                                <button type="submit" class="delete-action" onclick="return confirm('Cancel this membership subscription?')">
                                                    <i class="fas fa-ban"></i> Cancel Membership
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="11">No membership transaction records found.</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
                <?php echo renderPagination($total_records, $current_page, $records_per_page, 'membership_details.php'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="membershipViewModal" class="membership-view-modal">
    <div class="membership-view-card">
        <div class="membership-view-header">
            <h3 id="membershipViewTitle">Membership Details</h3>
            <button type="button" class="membership-view-close" onclick="closeMembershipViewModal()">&times;</button>
        </div>
        <div class="membership-view-body" id="membershipViewBody">
            Loading...
        </div>
    </div>
</div>

<script>
    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openMembershipViewModal(data) {
        const modal = document.getElementById('membershipViewModal');
        const body = document.getElementById('membershipViewBody');
        const title = document.getElementById('membershipViewTitle');

        const featureList = Array.isArray(data.features) && data.features.length
            ? `<ul class="membership-features">${data.features.map((f) => `<li>${escapeHtml(f)}</li>`).join('')}</ul>`
            : '<p style="margin:8px 0 0;font-size:13px;color:#6b7280;">No feature data available.</p>';

        const cancelledRow = data.cancelled_at
            ? `<div class="membership-info-row"><span>Cancelled At</span><strong>${escapeHtml(data.cancelled_at)}</strong></div>`
            : '';
        const cancelledByRow = data.cancelled_by
            ? `<div class="membership-info-row"><span>Cancelled By</span><strong>${escapeHtml(data.cancelled_by)}</strong></div>`
            : '';

        title.textContent = `Membership Transaction #${data.transaction_id || '-'}`;
        body.innerHTML = `
            <div class="membership-view-grid">
                <div class="membership-view-block">
                    <h4>Subscription Info</h4>
                    <div class="membership-info-row"><span>User</span><strong>${escapeHtml(data.user_name || '-')}</strong></div>
                    <div class="membership-info-row"><span>Email</span><strong>${escapeHtml(data.user_email || '-')}</strong></div>
                    <div class="membership-info-row"><span>Phone</span><strong>${escapeHtml(data.user_phone || '-')}</strong></div>
                    <div class="membership-info-row"><span>Membership ID</span><strong>${escapeHtml(data.membership_id ?? '-')}</strong></div>
                    <div class="membership-info-row"><span>Membership Name</span><strong>${escapeHtml(data.membership_name || '-')}</strong></div>
                    <div class="membership-info-row"><span>Billing</span><strong>${escapeHtml(data.billing_plan || '-')}</strong></div>
                    <div class="membership-info-row"><span>Status</span><strong>${escapeHtml(data.status || '-')}</strong></div>
                </div>
                <div class="membership-view-block">
                    <h4>Dates & Remaining</h4>
                    <div class="membership-info-row"><span>Subscribed At</span><strong>${escapeHtml(data.subscribed_at || '-')}</strong></div>
                    <div class="membership-info-row"><span>Start Date</span><strong>${escapeHtml(data.start_date || '-')}</strong></div>
                    <div class="membership-info-row"><span>End Date</span><strong>${escapeHtml(data.end_date || '-')}</strong></div>
                    <div class="membership-info-row"><span>Remaining Days</span><strong>${escapeHtml(data.remaining_days ?? 0)} day(s)</strong></div>
                    ${cancelledRow}
                    ${cancelledByRow}
                </div>
                <div class="membership-view-block">
                    <h4>Payment Info</h4>
                    <div class="membership-info-row"><span>Payment ID</span><strong>${escapeHtml(data.payment_id ?? '-')}</strong></div>
                    <div class="membership-info-row"><span>Card Holder</span><strong>${escapeHtml(data.card_holder_name || '-')}</strong></div>
                    <div class="membership-info-row"><span>Method</span><strong>${escapeHtml(data.payment_method || '-')}</strong></div>
                    <div class="membership-info-row"><span>Amount</span><strong>₹ ${escapeHtml(data.amount || '0.00')}</strong></div>
                    <div class="membership-info-row"><span>Stripe Ref</span><strong style="font-size:12px;">${escapeHtml(data.stripe_intent || '-')}</strong></div>
                    <div class="membership-info-row"><span>Description</span><strong>${escapeHtml(data.payment_note || '-')}</strong></div>
                </div>
                <div class="membership-view-block">
                    <h4>Plan Features</h4>
                    ${featureList}
                </div>
            </div>
        `;

        modal.classList.add('show');
        closeAllActionDropdowns();
    }

    function closeMembershipViewModal() {
        document.getElementById('membershipViewModal').classList.remove('show');
    }

    document.addEventListener('click', function(event) {
        const modal = document.getElementById('membershipViewModal');
        if (event.target === modal) {
            closeMembershipViewModal();
        }
    });
</script>
</body>
</html>
