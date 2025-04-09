<?php
include('connect.php');


$ah_id = $_GET['ah_id'];
$ah_id = mysqli_real_escape_string($con, $ah_id);


$update_history_query = "UPDATE appointment_history SET ah_status='Accepted' WHERE ah_id='$ah_id'";
$update_history_result = mysqli_query($con, $update_history_query);

if ($update_history_result) {
    $select_a_id_query = "SELECT a_id FROM appointment_history WHERE ah_id='$ah_id'";
    $a_id_result = mysqli_query($con, $select_a_id_query);
    $a_id_row = mysqli_fetch_assoc($a_id_result);
    $a_id = $a_id_row['a_id'];

    
    $update_appointments_query = "UPDATE appointments SET a_status='Accepted' WHERE a_id='$a_id'";
    $update_appointments_result = mysqli_query($con, $update_appointments_query);

    if ($update_appointments_result) {
        header('Location:appointments_manage.php');
    } else {
        echo "Error updating appointments table: " . mysqli_error($con);
    }
} else {
    echo "Error updating appointment_history table: " . mysqli_error($con);
}


mysqli_close($con);
?>
