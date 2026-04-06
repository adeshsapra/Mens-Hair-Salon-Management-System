<?php
include 'connect.php';
include 'header.php';
require_once __DIR__ . '/../payment_integration_helpers.php';

$user_id = (int) $_SESSION['user_id'];
$descriptions = [];
$tableReady = paymentIntegrationMembershipTransactionsReady($con);

if ($tableReady) {
    mysqli_query(
        $con,
        "UPDATE membership_transactions
         SET status = 'expired'
         WHERE status = 'active' AND end_date < CURDATE()"
    );

    $txStmt = mysqli_prepare(
        $con,
        "SELECT
            mt.mp_id,
            mt.membership_name,
            mt.billing_plan,
            mt.end_date,
            mt.status,
            mt.subscribed_at,
            mp.features_json,
            mp.display_name
         FROM membership_transactions mt
         LEFT JOIN membership_plans mp ON mp.mp_id = mt.mp_id
         WHERE mt.user_id = ?
         ORDER BY mt.subscribed_at DESC, mt.mt_id DESC"
    );
    mysqli_stmt_bind_param($txStmt, 'i', $user_id);
    mysqli_stmt_execute($txStmt);
    $txResult = mysqli_stmt_get_result($txStmt);

    $groups = [];
    while ($row = mysqli_fetch_assoc($txResult)) {
        $name = trim((string) ($row['membership_name'] ?? ''));
        $displayName = trim((string) ($row['display_name'] ?? 'Membership'));
        $billing = strtolower(trim((string) ($row['billing_plan'] ?? 'monthly')));
        if (!in_array($billing, ['monthly', 'yearly'], true)) {
            $billing = 'monthly';
        }

        if ($name === '') {
            $name = ($billing === 'yearly' ? 'Yearly ' : 'Monthly ') . $displayName;
        }

        $normalizedStatus = paymentIntegrationNormalizeMembershipStatus(
            (string) ($row['status'] ?? 'active'),
            (string) ($row['end_date'] ?? '')
        );
        $remainingDays = paymentIntegrationMembershipRemainingDays(
            (string) ($row['end_date'] ?? ''),
            $normalizedStatus
        );

        $features = json_decode((string) ($row['features_json'] ?? '[]'), true);
        if (!is_array($features)) {
            $features = [];
        }

        $subscribedAt = trim((string) ($row['subscribed_at'] ?? ''));
        $ts = strtotime($subscribedAt);
        if ($ts === false) {
            $ts = time();
        }

        if (!isset($groups[$name]) || $ts > $groups[$name]['ts']) {
            $groups[$name] = [
                'ts' => $ts,
                'descriptions' => $features,
                'remaining_days' => $remainingDays,
                'end_date' => (string) ($row['end_date'] ?? ''),
                'status' => $normalizedStatus,
            ];
        }
    }
    mysqli_stmt_close($txStmt);

    $descriptions = $groups;
}
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Memberships</h1>
        <a href="../membership.php" class="app_more" style="margin-top: 0;"><i class="fas fa-crown"></i> Upgrade</a>
    </div>

    <div class="description-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
        <?php if (!$tableReady): ?>
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3);">
                <i class="fas fa-tools" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i>
                <h3>Membership setup pending</h3>
                <p style="color: #777; margin-bottom: 20px;">Admin needs to run <code>setup_membership_transactions_table.php</code> once.</p>
            </div>
        <?php elseif (!empty($descriptions)): ?>
            <?php foreach ($descriptions as $plan => $data): ?>
                <div class='membership-plan' style="background-color: var(--white); padding: 32px; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(203,185,15,0.2); position: relative; overflow: hidden;">
                    <i class="fas fa-crown" style="position: absolute; top: -10px; right: -10px; font-size: 100px; color: var(--brand); opacity: 0.05;"></i>

                    <h2 style="font-size: 24px; color: var(--bg1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-gem" style="color: var(--brand);"></i> <?= htmlspecialchars($plan) ?>
                    </h2>

                    <div class="details" style="margin-bottom: 24px; background: #faf9f5; padding: 16px; border-radius: 12px; max-height: none; overflow: visible;">
                        <?php if (!empty($data['descriptions'])): ?>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($data['descriptions'] as $description): ?>
                                    <li style="margin-bottom: 8px; color: #555; display: flex; align-items: flex-start; gap: 10px;">
                                        <i class="fas fa-check" style="color: #1e8e3e; margin-top: 4px;"></i>
                                        <span><?= htmlspecialchars((string) $description) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="margin:0;color:#666;">Plan feature details are not available for this purchase.</p>
                        <?php endif; ?>
                    </div>

                    <style>
                        @media (max-width: 768px) {
                            .membership-info-row { flex-direction: column !important; align-items: flex-start !important; gap: 10px; }
                        }
                    </style>
                    <div class="membership-status" style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
                        <?php
                        $status = strtolower((string) ($data['status'] ?? 'expired'));
                        if ($status === 'cancelled') {
                            echo "<div style='background: #fdecea; color: #b42318; padding: 16px; border-radius: 12px; text-align: center;'>";
                            echo "<i class='fas fa-ban' style='font-size: 20px; margin-bottom: 8px; display: block;'></i>";
                            echo "<strong>This membership was cancelled by admin.</strong><br>";
                            echo "<span style='font-size:13px;'>Ended on " . htmlspecialchars((string) ($data['end_date'] ?? '-')) . "</span>";
                            echo "</div>";
                        } elseif ($data['remaining_days'] > 0 && $status === 'active') {
                            echo "<div class='membership-info-row' style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>";
                            echo "<span style='background: #e6f4ea; color: #1e8e3e; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: 600; white-space: nowrap;'><i class='fas fa-check-circle'></i> Active</span>";
                            echo "<span style='font-size: 14px; color: #666; white-space: nowrap;'><i class='far fa-calendar-alt'></i> Ends: <strong>" . htmlspecialchars((string) ($data['end_date'] ?? '-')) . "</strong></span>";
                            echo "</div>";

                            echo "<div style='background: var(--bg1); color: var(--brand); padding: 12px; border-radius: 8px; text-align: center; font-weight: 500;'>";
                            echo "<i class='fas fa-clock'></i> <strong>" . htmlspecialchars((string) $data['remaining_days']) . " days remaining</strong>";
                            echo "</div>";
                        } else {
                            echo "<div style='background: #fce8e6; color: #d93025; padding: 16px; border-radius: 12px; text-align: center;'>";
                            echo "<i class='fas fa-exclamation-circle' style='font-size: 20px; margin-bottom: 8px; display: block;'></i>";
                            echo "<strong>Your membership has expired.</strong>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3);">
                <i class="fas fa-sad-cry" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i>
                <h3>No active subscription</h3>
                <p style="color: #777; margin-bottom: 20px;">You are currently not subscribed to any membership plans.</p>
                <a href="../membership.php" class="app_more" style="display: inline-block;"><i class="fas fa-crown"></i> View Plans</a>
            </div>
        <?php endif; ?>
    </div>
</main>
