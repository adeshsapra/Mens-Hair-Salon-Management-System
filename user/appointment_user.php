<?php

include ('header.php'); 
include('connect.php');


$username = $_SESSION['username'];
$app_query = "SELECT * FROM appointments WHERE a_name = '$username'";
$app_result = mysqli_query($con,$app_query);


?>
<main class="content">

            <section class="appointments">
                <h1>Manage Here Your Appointments:</h1>
                <?php while($app_row = mysqli_fetch_assoc($app_result)){
                    ?>
                <div class="appointment">
                    <h2><?php echo $app_row['a_category'];?></h2>
                    <p>Service: <?php echo $app_row['a_type'];?></p>
                    <p>Date: <?php echo $app_row['a_date'];?></p>
                    <p>Time: <?php echo $app_row['a_time'];?></p>

                    <!-- <p>Status: <?php //echo $app_row['a_status'];?></p> -->
                    <!-- <a href="../appointment.php"><button type="button">Reschedule</button></a>
                     <a href="delete_appointment.php?id=<?php //echo $app_row['a_id'];?>" onclick="return confirm('Are you sure you want to delete this User?');"><button type="button">Cancel
                    </button></a> 
                </div> -->
                    
                <?php
                $status = $app_row['a_status'];
                if ($status === 'Accepted') {
                    echo '<h4>Status: Accepted</h4>';
                } elseif ($status === 'Cancelled') {
                    echo '<h4>Status: Cancelled</h4>';
                } else {
                    echo '<h4>Status: Pending</h4>';
                    ?>
                    <a href="../appointment.php?id=<?php echo $app_row['a_id']; ?>"><button type="button">Reschedule</button></a>
                    <a href="delete_appointment.php?id=<?php echo $app_row['a_id']; ?>" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                        <button type="button">Cancel</button>
                    </a>
                    <?php
                }
                ?>
            </div>
        <?php } ?>
        <div id="app_more">
        <a href="../appointment.php" class="app_more">Book More Appointments</a>
        </div>
            </section>
</main>