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
    <h1>Subscribed Membership Details</h1>
    <div class="description-container">
        <?php if (!empty($descriptions)): ?>
            <?php foreach ($descriptions as $plan => $data): ?>
                <div class='membership-plan'>
                    <h2><?= htmlspecialchars($plan) ?></h2>
                    <div class="details">
                        <ul>
                            <?php foreach ($data['descriptions'] as $description): ?>
                                <li><?= htmlspecialchars($description) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <p class="membership-status">
                        <?php 
                        if ($data['remaining_days'] > 0) {
                            echo "<span style='color:green;font-size:20px;font-weight:700;'>" .$data['status'] . "</span><br>";
                            echo "<strong>Days remaining: </strong><span class='remaining-days'>" . $data['remaining_days'] . "</span>";
                            echo "<br><strong>Membership ends on: </strong><span class='end-date'>" . $data['end_date'] . "</span>";
                        } else {
                            echo "<span class='expired'>Your membership has expired.</span>";
                        }
                        ?>
                    </p>
                    <!--<button class="cancel-button">Cancel Membership</button> -->
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No membership descriptions available.</p>
        <?php endif; ?>
    </div>
</main>
