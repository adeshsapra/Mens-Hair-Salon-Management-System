<?php
include('connect.php');

if (isset($_POST['id'])) {
    $order_id = mysqli_real_escape_string($con, $_POST['id']);
    
    $delete_query = "DELETE FROM product_sales WHERE s_id = '$order_id' AND s_status = 'Cancelled'";
    
    if (mysqli_query($con, $delete_query)) {
        header("Location:order.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }
} else {
    echo "Invalid request.";
}
?>
