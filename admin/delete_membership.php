<?php
    include('connect.php');

    $royal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $royal = "DELETE FROM royal_membership WHERE royal_id = $royal_id";
    $royal_data = mysqli_query($con,$royal);
    if($royal_data){
        header("Location:membership_manage.php");
    }

    $classic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $classic = "DELETE FROM classic_membership WHERE classic_id = $classic_id";
    $classic_data = mysqli_query($con,$classic);
    if($classic_data){
        header("Location:membership_manage.php");
    }

    $standard_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $standard = "DELETE FROM standard_membership WHERE standard_id = $standard_id";
    $standard_data = mysqli_query($con,$standard);
    if($standard_data){
        header("Location:membership_manage.php");
    }
?>