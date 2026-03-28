<?php
include('connect.php');

if (isset($_GET['pay_id'])) {
    $pay_id = $_GET['pay_id'];
    
    if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
        $new_status = 'Received';
        $msg = 'Payment confirmed successfully!';
    } else {
        $new_status = 'Failed';
        $msg = 'Payment discarded successfully!';
    }

    $update_query = "UPDATE payment SET p_status = '$new_status' WHERE pay_id = $pay_id";

    if (mysqli_query($con, $update_query)) {
        header("Location: payment_manage.php?toast=success&msg=" . urlencode($msg)); 
    } else {
        header("Location: payment_manage.php?toast=error&msg=" . urlencode('Error updating status: ' . mysqli_error($con)));
    }
    exit();
}
?>
