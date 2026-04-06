<?php

include 'header.php';

include 'connect.php';
require_once __DIR__ . '/../payment_integration_helpers.php';
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <?php
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $query = "SELECT username, last_login FROM user_reg WHERE id = '$user_id'";
                $result = mysqli_query($con, $query);
                $row = mysqli_fetch_assoc($result);

                $username = $row['username'];
                $last_login = $row['last_login'] ? date('F j, Y', strtotime($row['last_login'])) : 'Never';

                echo '<h1 style="margin: 0; font-size: 28px;">Hello, ' . htmlspecialchars($username) . '! <span style="font-size: 20px; font-weight: normal; color: #777;">Welcome back.</span></h1>';
            }
            ?>
        </div>
        <div style="background: var(--bg2); color: var(--bg1); padding: 8px 16px; border-radius: 30px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-calendar-day" style="margin-right: 6px;"></i> <?php echo date('D, M j'); ?>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <section class="overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 2.5rem;">
        
        <div class="card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;">
            <i class="fas fa-calendar-check" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: var(--brand); opacity: 0.05;"></i>
            <h2 style="font-size: 18px; color: #555; margin-bottom: 12px; font-weight: 600;"><i class="fas fa-clock" style="color: var(--brand); margin-right: 10px;"></i> Upcoming</h2>
            <?php
            if (isset($user_id)) {
                $appointment_query = "SELECT COUNT(*) as total FROM appointments WHERE id = '$user_id' AND a_status = 'Accepted'";
                $appointment_result = mysqli_query($con, $appointment_query);
                $appointment_row = mysqli_fetch_assoc($appointment_result);
                $total_appointments = $appointment_row['total'];
                echo '<p style="font-size: 24px; color: var(--bg1); font-weight: 700; margin: 0;">' . intval($total_appointments) . ' Appointments</p>';
                echo '<p style="margin: 8px 0 0; color: #777; font-size: 14px;">Confirmed and pending visits.</p>';
            }
            ?>
        </div>
       
        <div class="card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;">
            <i class="fas fa-crown" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: var(--brand); opacity: 0.05;"></i>
            <h2 style="font-size: 18px; color: #555; margin-bottom: 12px; font-weight: 600;"><i class="fas fa-gem" style="color: var(--brand); margin-right: 10px;"></i> Membership</h2>
            <?php
            if (isset($user_id)) {
                $membership_query = "
                    SELECT mt.membership_name, mt.status, mt.end_date
                    FROM membership_transactions mt
                    WHERE mt.user_id = '$user_id'
                      AND LOWER(TRIM(mt.status)) = 'active'
                      AND mt.end_date >= CURDATE()
                    ORDER BY mt.subscribed_at DESC, mt.mt_id DESC
                    LIMIT 1
                ";
                if (!paymentIntegrationMembershipTransactionsReady($con)) {
                    $membership_query = "
                        SELECT payment_note AS membership_name, p_status AS status, p_date AS end_date
                        FROM payment
                        WHERE id = '$user_id'
                          AND (payment_for = 'membership' OR m_id IS NOT NULL)
                          AND LOWER(TRIM(p_status)) = 'active'
                        ORDER BY pay_id DESC
                        LIMIT 1
                    ";
                }
                $membership_result = mysqli_query($con, $membership_query);
                $membership_row = mysqli_fetch_assoc($membership_result);

                if ($membership_row) {
                    $membershipTitle = trim((string) ($membership_row['membership_name'] ?? 'Membership'));
                    echo '<p style="font-size: 24px; color: var(--bg1); font-weight: 700; margin: 0;">' . htmlspecialchars($membershipTitle) . '</p>';
                    echo '<p style="margin: 8px 0 0; color: #1e8e3e; font-size: 14px; font-weight: 600;"><i class="fas fa-check-circle"></i> Active Plan</p>';
                } else {
                    echo '<p style="font-size: 24px; color: var(--bg1); font-weight: 700; margin: 0;">Free Tier</p>';
                    echo '<p style="margin: 8px 0 0;"><a href="membership_user.php" class="premium-link">Upgrade Now <i class="fas fa-arrow-right" style="font-size: 11px;"></i></a></p>';
                }
            }
            ?>
        </div>

        <div class="card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;">
            <i class="fas fa-user-clock" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: var(--brand); opacity: 0.05;"></i>
            <h2 style="font-size: 18px; color: #555; margin-bottom: 12px; font-weight: 600;"><i class="fas fa-history" style="color: var(--brand); margin-right: 10px;"></i> Session</h2>
            <p style="font-size: 24px; color: var(--bg1); font-weight: 700; margin: 0;"><?php echo htmlspecialchars($last_login); ?></p>
            <p style="margin: 8px 0 0; color: #777; font-size: 14px;">Your last login date.</p>
        </div>
        
    </section>

    <h3 style="font-size: 20px; color: var(--bg1); font-weight: 700; margin-bottom: 1.5rem;">Quick Actions</h3>
    <section class="quick-actions" style="display: flex; gap: 16px; flex-wrap: wrap;">
        <a href="../appointment.php" class="app_more" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; border-radius: 12px; text-decoration: none; min-width: 200px; font-weight: 600;">
            <i class="fas fa-plus"></i> Book Appointment
        </a>
        <a href="../service.php" class="app_more" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; border-radius: 12px; text-decoration: none; min-width: 200px; font-weight: 600; background: var(--bg1); color: var(--brand);">
            <i class="fas fa-concierge-bell"></i> Our Services
        </a>
        <a href="../eshop.php" class="app_more" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; border-radius: 12px; text-decoration: none; min-width: 200px; font-weight: 600; background: #666; color: white;">
            <i class="fas fa-shopping-bag"></i> Shop Products
        </a>
    </section>
</main>
