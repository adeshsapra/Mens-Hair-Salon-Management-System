<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$con = mysqli_connect('localhost','root','','classycut');

?>
