<?php 
include 'header.php'; 
include 'connect.php';
?>

<main class="content">
    <header class="header">
        <div class="welcome-message">
        <?php
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $query = "SELECT username, last_login FROM user_reg WHERE id = '$user_id'";
            $result = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($result);

            $username = $row['username']; 
            $last_login = $row['last_login'] ? date('F j, Y, g:i a', strtotime($row['last_login'])) : 'Never';

            echo '<h1>Welcome, ' . htmlspecialchars($username) . '</h1>';
        }
        ?>
        </div>
        <div class="logout">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <section class="overview">
        
        <div class="card">
            <h2>Upcoming Appointments</h2>
            <?php
            if (isset($user_id)) {
                $appointment_query = "SELECT COUNT(*) as total FROM appointments WHERE id = '$user_id' AND a_status = 'Accepted'";
                $appointment_result = mysqli_query($con, $appointment_query);
                $appointment_row = mysqli_fetch_assoc($appointment_result);

                $total_appointments = $appointment_row['total'];
                echo '<p>You have ' . intval($total_appointments) . ' upcoming appointments.</p>';
            } else {
                echo '<p>Please log in to see your appointments.</p>';
            }
            ?>
        </div>
       
        <div class="card">
            <h2>Subscribed Memberships</h2>
            <?php
            if (isset($user_id)) {
                // Fetch the latest membership details
                $membership_query = "SELECT membership_type, payment_date, status FROM membership_payments WHERE id = '$user_id' ORDER BY payment_date DESC LIMIT 1";
                $membership_result = mysqli_query($con, $membership_query);
                $membership_row = mysqli_fetch_assoc($membership_result);

                if ($membership_row) {
                    $membership_type = $membership_row['membership_type'];
                    $payment_date = strtotime($membership_row['payment_date']);
                    $status = $membership_row['status'];
                    
                    // Define membership duration in days (for example, 30 days)
                    $membership_duration = 30; // Adjust this based on your actual durations
                    $end_date = $payment_date + ($membership_duration * 86400); // 86400 seconds in a day
                    $remaining_days = max(0, ($end_date - time()) / 86400); // Calculate remaining days
                    $formatted_end_date = date('F j, Y', $end_date);

                    echo '<p>Type: ' . htmlspecialchars($membership_type) . '</p>';
                    echo '<p>Days Remaining: ' . intval($remaining_days) . '</p>';
                    echo '<p>Ends On: ' . htmlspecialchars($formatted_end_date) . '</p>';
                } else {
                    echo '<p>You do not have an active membership.</p>';
                }
            } else {
                echo '<p>Please log in to see your membership details.</p>';
            }
            ?>
        </div>

        <div class="card">
            <h2>Recent Activity</h2>
            <p>Last login: <?php echo isset($last_login) ? htmlspecialchars($last_login) : 'N/A'; ?></p>
        </div>
        
    </section>
    <section class="quick-actions">
        <a href="../appointment.php">Book Appointment</a>
        <a href="../service.php">View Service</a>
    </section>
</main>
