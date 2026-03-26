<?php 
include 'connect.php';
include 'header.php'; 

$user_id = $_SESSION['user_id'];

// Updated SQL query to include the status field
$query = "
    SELECT mp.membership_type,
           rm.royal_desc,
           cm.classic_desc,
           sm.standard_desc,
           mp.payment_date,
           mp.status
    FROM membership_payments mp
    LEFT JOIN royal_membership rm ON rm.royal_plan = CASE WHEN mp.membership_type LIKE '%Yearly%' THEN 'yearly' ELSE 'monthly' END
    LEFT JOIN classic_membership cm ON cm.classic_plan = CASE WHEN mp.membership_type LIKE '%Yearly%' THEN 'yearly' ELSE 'monthly' END
    LEFT JOIN standard_membership sm ON sm.standard_plan = CASE WHEN mp.membership_type LIKE '%Yearly%' THEN 'yearly' ELSE 'monthly' END
    WHERE id = $user_id
";

$result = $con->query($query);

$descriptions = [];

while ($membership_fetch_row = $result->fetch_assoc()) {
    $plan_name = '';
    $description = '';
    $payment_date = new DateTime($membership_fetch_row['payment_date']);

    if (strpos($membership_fetch_row['membership_type'], 'Royal') !== false) {
        $plan_name = strpos($membership_fetch_row['membership_type'], 'Yearly') !== false ? 'Royal (Yearly)' : 'Royal (Monthly)';
        $description = $membership_fetch_row['royal_desc'];
    } elseif (strpos($membership_fetch_row['membership_type'], 'Classic') !== false) {
        $plan_name = strpos($membership_fetch_row['membership_type'], 'Yearly') !== false ? 'Classic (Yearly)' : 'Classic (Monthly)';
        $description = $membership_fetch_row['classic_desc'];
    } elseif (strpos($membership_fetch_row['membership_type'], 'Standard') !== false) {
        $plan_name = strpos($membership_fetch_row['membership_type'], 'Yearly') !== false ? 'Standard (Yearly)' : 'Standard (Monthly)';
        $description = $membership_fetch_row['standard_desc'];
    }

    $end_date = clone $payment_date;
    if (strpos($membership_fetch_row['membership_type'], 'Yearly') !== false) {
        $end_date->modify('+1 year');
    } elseif (strpos($membership_fetch_row['membership_type'], 'Monthly') !== false) {
        $end_date->modify('+1 month');
    }

    $now = new DateTime();
    $remaining_days = max(0, $now < $end_date ? $now->diff($end_date)->days : 0);

    // Capture the status from the database
    $status = $membership_fetch_row['status'];

    if (!isset($descriptions[$plan_name])) {
        $descriptions[$plan_name] = [
            'descriptions' => [],
            'remaining_days' => $remaining_days,
            'end_date' => $end_date->format('Y-m-d'),
            'status' => $status // Store the status here
        ];
    }

    if (!in_array($description, $descriptions[$plan_name]['descriptions'])) {
        $descriptions[$plan_name]['descriptions'][] = $description;
    }
}
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Memberships</h1>
        <a href="../membership.php" class="app_more" style="margin-top: 0;"><i class="fas fa-crown"></i> Upgrade</a>
    </div>

    <div class="description-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;">
        <?php if (!empty($descriptions)): ?>
            <?php foreach ($descriptions as $plan => $data): ?>
                <div class='membership-plan' style="background-color: var(--white); padding: 32px; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(203,185,15,0.2); position: relative; overflow: hidden;">
                    
                    <!-- Decorative Crown Background -->
                    <i class="fas fa-crown" style="position: absolute; top: -10px; right: -10px; font-size: 100px; color: var(--brand); opacity: 0.05;"></i>

                    <h2 style="font-size: 24px; color: var(--bg1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-gem" style="color: var(--brand);"></i> <?= htmlspecialchars($plan) ?>
                    </h2>
                    
                    <div class="details" style="margin-bottom: 24px; background: #faf9f5; padding: 16px; border-radius: 12px;">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($data['descriptions'] as $description): ?>
                                <li style="margin-bottom: 8px; color: #555; display: flex; align-items: flex-start; gap: 10px;">
                                    <i class="fas fa-check" style="color: #1e8e3e; margin-top: 4px;"></i> 
                                    <span><?= htmlspecialchars($description) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="membership-status" style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
                        <?php 
                        if ($data['remaining_days'] > 0) {
                            $status_color = (strtolower($data['status']) == 'active') ? '#1e8e3e' : '#b06000';
                            $status_bg = (strtolower($data['status']) == 'active') ? '#e6f4ea' : '#fef7e0';
                            $status_icon = (strtolower($data['status']) == 'active') ? 'fa-check-circle' : 'fa-hourglass-half';
                            
                            echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>";
                            echo "<span style='background: {$status_bg}; color: {$status_color}; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: 600;'><i class='fas {$status_icon}'></i> " . htmlspecialchars($data['status']) . "</span>";
                            echo "<span style='font-size: 14px; color: #666;'><i class='far fa-calendar-alt'></i> Ends: <strong>" . htmlspecialchars($data['end_date']) . "</strong></span>";
                            echo "</div>";
                            
                            echo "<div style='background: var(--bg1); color: var(--brand); padding: 12px; border-radius: 8px; text-align: center; font-weight: 500;'>";
                            echo "<i class='fas fa-clock'></i> <strong>" . htmlspecialchars($data['remaining_days']) . " days remaining</strong>";
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
