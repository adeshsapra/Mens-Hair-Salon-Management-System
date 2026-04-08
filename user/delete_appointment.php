<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connect.php';
require_once '../notification_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$appointmentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($appointmentId <= 0) {
    header('Location: appointment_user.php?toast=error&msg=Invalid+appointment+request.');
    exit();
}

$rowResult = mysqli_query(
    $con,
    "SELECT a_id, a_name, a_date, a_time, a_status
     FROM appointments
     WHERE a_id = {$appointmentId} AND id = {$userId}
     LIMIT 1"
);
$appointment = $rowResult ? mysqli_fetch_assoc($rowResult) : null;

if (!$appointment) {
    header('Location: appointment_user.php?toast=error&msg=Appointment+not+found.');
    exit();
}

mysqli_begin_transaction($con);

try {
    $updateAppointment = mysqli_query(
        $con,
        "UPDATE appointments SET a_status = 'Cancelled' WHERE a_id = {$appointmentId} AND id = {$userId}"
    );
    if (!$updateAppointment) {
        throw new Exception('Failed to cancel appointment.');
    }

    mysqli_query(
        $con,
        "UPDATE appointment_history SET ah_status = 'Cancelled' WHERE a_id = {$appointmentId}"
    );

    mysqli_commit($con);

    $appDate = trim((string) ($appointment['a_date'] ?? ''));
    $appTime = trim((string) ($appointment['a_time'] ?? ''));
    notificationCreateForUser(
        $con,
        $userId,
        'appointment_status_updated',
        'Appointment Cancelled',
        "You cancelled appointment #{$appointmentId} scheduled on {$appDate} at {$appTime}.",
        'user/appointment_user.php',
        'user',
        $userId,
        'appointment',
        $appointmentId
    );
    notificationCreateForAllAdmins(
        $con,
        'appointment_status_updated',
        'Appointment Cancelled by User',
        "User #{$userId} cancelled appointment #{$appointmentId}.",
        'admin/appointments_manage.php',
        'user',
        $userId,
        'appointment',
        $appointmentId
    );

    header('Location: appointment_user.php?toast=success&msg=Appointment+cancelled+successfully!');
    exit();
} catch (Exception $e) {
    mysqli_rollback($con);
    header('Location: appointment_user.php?toast=error&msg=' . urlencode($e->getMessage()));
    exit();
}

