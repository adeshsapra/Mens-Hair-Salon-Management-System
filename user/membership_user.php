<?php
include 'connect.php';
include 'header.php';

$user_id = (int) $_SESSION['user_id'];

function membership_resolve_public_type(string $type): array
{
    $isYearly = stripos($type, 'Yearly') !== false || stripos($type, 'Annual') !== false;
    $billing = $isYearly ? 'yearly' : 'monthly';
    $pass = null;
    if (stripos($type, 'Royal') !== false) {
        $pass = 'royal';
    } elseif (stripos($type, 'Classic') !== false) {
        $pass = 'classic';
    } elseif (stripos($type, 'Standard') !== false) {
        $pass = 'standard';
    }
    return [$pass, $billing];
}

function membership_user_plan_label(string $pass, string $billing): string
{
    $name = $pass === 'royal' ? 'Royal' : ($pass === 'classic' ? 'Classic' : 'Standard');
    $b = $billing === 'yearly' ? 'Yearly' : 'Monthly';
    return $name . ' (' . $b . ')';
}

$pay_stmt = mysqli_prepare(
    $con,
    'SELECT membership_type, payment_date, status FROM membership_payments WHERE id = ? ORDER BY payment_date DESC'
);
mysqli_stmt_bind_param($pay_stmt, 'i', $user_id);
mysqli_stmt_execute($pay_stmt);
$pay_res = mysqli_stmt_get_result($pay_stmt);

$groups = [];
while ($row = mysqli_fetch_assoc($pay_res)) {
    [$pass, $billing] = membership_resolve_public_type((string) $row['membership_type']);
    if ($pass === null) {
        continue;
    }
    $plan_name = membership_user_plan_label($pass, $billing);
    $ts = strtotime($row['payment_date']);
    if (!isset($groups[$plan_name]) || $ts > $groups[$plan_name]['ts']) {
        $groups[$plan_name] = [
            'row' => $row,
            'pass' => $pass,
            'billing' => $billing,
            'ts' => $ts,
        ];
    }
}
mysqli_stmt_close($pay_stmt);

$descriptions = [];
foreach ($groups as $plan_name => $g) {
    $feature_list = [];
    $feat_stmt = mysqli_prepare(
        $con,
        'SELECT features_json FROM membership_plans WHERE pass_key = ? AND billing_plan = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($feat_stmt, 'ss', $g['pass'], $g['billing']);
    mysqli_stmt_execute($feat_stmt);
    $feat_res = mysqli_stmt_get_result($feat_stmt);
    if ($feat_row = mysqli_fetch_assoc($feat_res)) {
        $decoded = json_decode($feat_row['features_json'], true);
        $feature_list = is_array($decoded) ? $decoded : [];
    }
    mysqli_stmt_close($feat_stmt);

    $payment_date = new DateTime($g['row']['payment_date']);
    $end_date = clone $payment_date;
    if ($g['billing'] === 'yearly') {
        $end_date->modify('+1 year');
    } else {
        $end_date->modify('+1 month');
    }
    $now = new DateTime();
    $remaining_days = max(0, $now < $end_date ? $now->diff($end_date)->days : 0);

    $descriptions[$plan_name] = [
        'descriptions' => $feature_list,
        'remaining_days' => $remaining_days,
        'end_date' => $end_date->format('Y-m-d'),
        'status' => $g['row']['status'],
    ];
}
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Memberships</h1>
        <a href="../membership.php" class="app_more" style="margin-top: 0;"><i class="fas fa-crown"></i> Upgrade</a>
    </div>

    <div class="description-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
        <?php if (!empty($descriptions)): ?>
            <?php foreach ($descriptions as $plan => $data): ?>
                <div class='membership-plan' style="background-color: var(--white); padding: 32px; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(203,185,15,0.2); position: relative; overflow: hidden;">
                    
                    <!-- Decorative Crown Background -->
                    <i class="fas fa-crown" style="position: absolute; top: -10px; right: -10px; font-size: 100px; color: var(--brand); opacity: 0.05;"></i>

                    <h2 style="font-size: 24px; color: var(--bg1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-gem" style="color: var(--brand);"></i> <?= htmlspecialchars($plan) ?>
                    </h2>
                    
                    <div class="details" style="margin-bottom: 24px; background: #faf9f5; padding: 16px; border-radius: 12px; max-height: none; overflow: visible;">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($data['descriptions'] as $description): ?>
                                <li style="margin-bottom: 8px; color: #555; display: flex; align-items: flex-start; gap: 10px;">
                                    <i class="fas fa-check" style="color: #1e8e3e; margin-top: 4px;"></i> 
                                    <span><?= htmlspecialchars($description) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <style>
                        @media (max-width: 768px) {
                            .membership-info-row { flex-direction: column !important; align-items: flex-start !important; gap: 10px; }
                        }
                    </style>
                    <div class="membership-status" style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
                        <?php 
                        if ($data['remaining_days'] > 0) {
                            $status_color = (strtolower($data['status']) == 'active') ? '#1e8e3e' : '#b06000';
                            $status_bg = (strtolower($data['status']) == 'active') ? '#e6f4ea' : '#fef7e0';
                            $status_icon = (strtolower($data['status']) == 'active') ? 'fa-check-circle' : 'fa-hourglass-half';
                            
                            echo "<div class='membership-info-row' style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>";
                            echo "<span style='background: {$status_bg}; color: {$status_color}; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: 600; white-space: nowrap;'><i class='fas {$status_icon}'></i> " . htmlspecialchars($data['status']) . "</span>";
                            echo "<span style='font-size: 14px; color: #666; white-space: nowrap;'><i class='far fa-calendar-alt'></i> Ends: <strong>" . htmlspecialchars($data['end_date']) . "</strong></span>";
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
