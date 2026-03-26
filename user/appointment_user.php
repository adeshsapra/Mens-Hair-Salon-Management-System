<?php

include ('header.php'); 
include('connect.php');


$username = $_SESSION['username'];
$app_query = "SELECT * FROM appointments WHERE a_name = '$username'";
$app_result = mysqli_query($con,$app_query);


?>
<main class="content">

            <section class="appointments">
                <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h1 style="margin-bottom: 0;">Manage Your Appointments</h1>
                    <a href="../appointment.php" class="app_more" style="margin-top: 0;"><i class="fas fa-plus"></i> Book New</a>
                </div>
                
                <div class="appointments-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                    <?php 
                    if(mysqli_num_rows($app_result) > 0) {
                        while($app_row = mysqli_fetch_assoc($app_result)){
                    ?>
                        <div class="appointment" style="margin-bottom: 0; position: relative;">
                            <div class="app-icon" style="position: absolute; right: 24px; top: 24px; color: var(--brand); font-size: 24px; opacity: 0.2;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            
                            <h2><?php echo htmlspecialchars($app_row['a_category']);?></h2>
                            <p><i class="fas fa-cut" style="color: var(--brand); margin-right: 8px; width: 16px;"></i> <strong>Service:</strong> <?php echo htmlspecialchars($app_row['a_type']);?></p>
                            <p><i class="far fa-clock" style="color: var(--brand); margin-right: 8px; width: 16px;"></i> <strong>Date:</strong> <?php echo htmlspecialchars($app_row['a_date']);?> at <?php echo htmlspecialchars($app_row['a_time']);?></p>

                            <div class="app-actions" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
                                <?php
                                $status = $app_row['a_status'];
                                if ($status === 'Accepted') {
                                    echo '<h4 style="margin: 0; background: #e6f4ea; color: #1e8e3e; border-radius: 20px; padding: 4px 12px; font-size: 13px;"><i class="fas fa-check-circle" style="margin-right: 4px;"></i> Accepted</h4>';
                                } elseif ($status === 'Cancelled') {
                                    echo '<h4 style="margin: 0; background: #fce8e6; color: #d93025; border-radius: 20px; padding: 4px 12px; font-size: 13px;"><i class="fas fa-times-circle" style="margin-right: 4px;"></i> Cancelled</h4>';
                                } else {
                                    echo '<h4 style="margin: 0; background: #fef7e0; color: #b06000; border-radius: 20px; padding: 4px 12px; font-size: 13px;"><i class="fas fa-hourglass-half" style="margin-right: 4px;"></i> Pending</h4>';
                                    ?>
                                    <div class="btn-group">
                                        <a href="../appointment.php?id=<?php echo $app_row['a_id']; ?>" title="Reschedule"><button type="button" style="padding: 6px 12px; font-size: 13px;"><i class="fas fa-edit"></i></button></a>
                                        <a href="delete_appointment.php?id=<?php echo $app_row['a_id']; ?>" onclick="return confirm('Are you sure you want to cancel this appointment?');" title="Cancel">
                                            <button type="button" style="padding: 6px 12px; font-size: 13px; background-color: #d93025; color: white;"><i class="fas fa-trash"></i></button>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    <?php 
                        }
                    } else {
                        echo '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3);"><i class="fas fa-calendar-times" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i><h3>No appointments found</h3><p style="color: #777;">You haven\'t booked any appointments yet.</p></div>';
                    }
                    ?>
                </div>
            </section>
</main>