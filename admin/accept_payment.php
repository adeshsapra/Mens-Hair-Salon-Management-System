<?php
include('connect.php');

if (isset($_GET['pay_id'])) {
    $pay_id = $_GET['pay_id'];
    
    if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
        $new_status = 'Received';
    } else {
        $new_status = 'Failed';
    }

    $update_query = "UPDATE payment SET p_status = '$new_status' WHERE pay_id = $pay_id";

    if (mysqli_query($con, $update_query)) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . mysqli_error($con);
    }
    header("Location: payment_manage.php"); 
    exit();
}
?>
