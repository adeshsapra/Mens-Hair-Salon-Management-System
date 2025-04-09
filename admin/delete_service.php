<?php
include('connect.php');

$haircut_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$haircut = "DELETE FROM haircut_service WHERE hair_id = $haircut_id";
$haircut_data = mysqli_query($con,$haircut);
if($haircut_data){
    header("Location:service_manage.php");
}


$beard_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$beard = "DELETE FROM beard_service WHERE beard_id = $beard_id";
$beard_data = mysqli_query($con,$beard);
if($beard_data){
    header("Location:service_manage.php");
}

$skin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$skin = "DELETE FROM skin_service WHERE skin_id = $skin_id";
$skin_data = mysqli_query($con,$skin);
if($skin_data){
    header("Location:service_manage.php");
}

$spa_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$spa = "DELETE FROM spa_service WHERE spa_id = $spa_id";
$spa_data = mysqli_query($con,$spa);
if($spa_data){
    header("Location:service_manage.php");
}




//  Customer Remove 

?>