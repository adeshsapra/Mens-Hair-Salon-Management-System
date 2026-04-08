<?php
include 'connect.php';
require_once '../notification_helpers.php';
require_once '../status_mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$actorAdminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : 0;
$ah_id = isset($_GET['ah_id']) ? (int) $_GET['ah_id'] : 0;

if ($ah_id <= 0) {
    header('Location: appointments_manage.php?toast=error&msg=Invalid+appointment+request.');
    exit();
}

$appointmentResult = mysqli_query(
    $con,
    "SELECT ah_id, a_id, id AS user_id, ah_name, ah_email, ah_date, ah_time
     FROM appointment_history
     WHERE ah_id = {$ah_id}
     LIMIT 1"
);
$appointmentRow = $appointmentResult ? mysqli_fetch_assoc($appointmentResult) : null;

if (!$appointmentRow) {
    header('Location: appointments_manage.php?toast=error&msg=Appointment+not+found.');
    exit();
}

$a_id = (int) $appointmentRow['a_id'];
$userId = (int) ($appointmentRow['user_id'] ?? 0);
$clientName = trim((string) ($appointmentRow['ah_name'] ?? 'User'));
$clientEmail = trim((string) ($appointmentRow['ah_email'] ?? ''));
$appDate = trim((string) ($appointmentRow['ah_date'] ?? ''));
$appTime = trim((string) ($appointmentRow['ah_time'] ?? ''));

$updateHistory = mysqli_query($con, "UPDATE appointment_history SET ah_status='Cancelled' WHERE ah_id={$ah_id}");
$updateAppointments = mysqli_query($con, "UPDATE appointments SET a_status='Cancelled' WHERE a_id={$a_id}");

if ($updateHistory && $updateAppointments) {
    if ($userId > 0) {
        notificationCreateForUser(
            $con,
            $userId,
            'appointment_status_updated',
            'Appointment Cancelled',
            "Your appointment #{$a_id} for {$appDate} at {$appTime} was cancelled by admin.",
            'user/appointment_user.php',
            'admin',
            $actorAdminId,
            'appointment',
            $a_id
        );
    }

    notificationCreateForAllAdmins(
        $con,
        'appointment_status_updated',
        'Appointment Cancelled',
        "{$clientName}'s appointment #{$a_id} was cancelled.",
        'admin/appointments_manage.php',
        'admin',
        $actorAdminId,
        'appointment',
        $a_id
    );

    sendAppointmentStatusEmail(
        $clientEmail,
        $clientName,
        $a_id,
        'Cancelled',
        $appDate,
        $appTime
    );

    header('Location: appointments_manage.php?toast=success&msg=Appointment+declined+successfully!');
    exit();
}

header('Location: appointments_manage.php?toast=error&msg=Failed+to+decline+appointment.');
exit();
