<?php 
include 'connect.php';

$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$admin = "DELETE FROM admin WHERE admin_id = $admin_id";
$admin_data = mysqli_query($con,$admin);
if($admin_data){
    header("Location:manage_admin.php");
}

?>