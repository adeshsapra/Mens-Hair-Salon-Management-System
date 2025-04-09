<?php
session_start();
include 'connect.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "UPDATE user_reg SET last_login = NOW() WHERE id = '$user_id'";
    mysqli_query($con, $query);
    session_destroy();
    header("Location:../index.php");
    exit();
}

?>
