<?php
session_start();
include('connect.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$deleted = false;

if ($id > 0) {
    if (mysqli_query($con, "DELETE FROM haircut_service WHERE hair_id = $id") && mysqli_affected_rows($con) > 0) {
        $deleted = true;
    }
    if (mysqli_query($con, "DELETE FROM beard_service WHERE beard_id = $id") && mysqli_affected_rows($con) > 0) {
        $deleted = true;
    }
    if (mysqli_query($con, "DELETE FROM skin_service WHERE skin_id = $id") && mysqli_affected_rows($con) > 0) {
        $deleted = true;
    }
    if (mysqli_query($con, "DELETE FROM spa_service WHERE spa_id = $id") && mysqli_affected_rows($con) > 0) {
        $deleted = true;
    }

    if ($deleted) {
        header("Location: service_manage.php?toast=success&msg=" . urlencode('Sub-Service deleted successfully!'));
        exit();
    } else {
        header("Location: service_manage.php?toast=error&msg=" . urlencode('No service was found to delete.'));
        exit();
    }
}

header("Location: service_manage.php");
exit();
?>
