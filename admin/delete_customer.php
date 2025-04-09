<?php 
include 'connect.php';

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = "DELETE FROM user_reg WHERE id = $user_id";
$user_data = mysqli_query($con,$user);
if($user_data){
    header("Location:customer.php");
}

?>