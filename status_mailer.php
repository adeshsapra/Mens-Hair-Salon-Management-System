<?php

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

if (!class_exists(PHPMailer::class)) {
    require_once __DIR__ . '/asset/PHPMailer.php';
    require_once __DIR__ . '/asset/SMTP.php';
    require_once __DIR__ . '/asset/Exception.php';
}

function statusMailerBuildEmailTemplate($title, $intro, array $rows, $footerNote = '')
{
    $safeTitle = htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8');
    $safeIntro = htmlspecialchars((string) $intro, ENT_QUOTES, 'UTF-8');
    $safeFooter = htmlspecialchars((string) $footerNote, ENT_QUOTES, 'UTF-8');

    $rowsHtml = '';
    foreach ($rows as $label => $value) {
        $safeLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $rowsHtml .=
            '<tr>' .
                '<td style="padding:10px 12px;border:1px solid #e5e7eb;background:#faf7e9;font-weight:700;color:#18150d;width:38%;">' . $safeLabel . '</td>' .
                '<td style="padding:10px 12px;border:1px solid #e5e7eb;color:#1f2937;">' . $safeValue . '</td>' .
            '</tr>';
    }

    return
        '<!doctype html>' .
        '<html><body style="margin:0;padding:0;background:#f5f3eb;font-family:Arial,Helvetica,sans-serif;color:#111827;">' .
            '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">' .
                '<tr><td align="center">' .
                    '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #ececec;">' .
                        '<tr>' .
                            '<td style="background:#18150d;padding:16px 20px;color:#eae3c2;font-size:20px;font-weight:700;">ClassyCut Salon</td>' .
                        '</tr>' .
                        '<tr>' .
                            '<td style="padding:22px 20px 12px 20px;">' .
                                '<h2 style="margin:0 0 10px 0;font-size:22px;line-height:1.3;color:#18150d;">' . $safeTitle . '</h2>' .
                                '<p style="margin:0;font-size:15px;line-height:1.6;color:#374151;">' . $safeIntro . '</p>' .
                            '</td>' .
                        '</tr>' .
                        '<tr>' .
                            '<td style="padding:10px 20px 20px 20px;">' .
                                '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">' .
                                    $rowsHtml .
                                '</table>' .
                            '</td>' .
                        '</tr>' .
                        '<tr>' .
                            '<td style="padding:0 20px 22px 20px;">' .
                                '<p style="margin:0;font-size:13px;line-height:1.5;color:#6b7280;">' . $safeFooter . '</p>' .
                            '</td>' .
                        '</tr>' .
                    '</table>' .
                '</td></tr>' .
            '</table>' .
        '</body></html>';
}

function statusMailerSend($toEmail, $toName, $subject, $title, $intro, array $rows, $footerNote = '')
{
    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'classycut007@gmail.com';
        $mail->Password = 'dgjg qxjo icve bita';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('classycut007@gmail.com', 'ClassyCut Salon');
        $mail->addAddress($toEmail, trim((string) $toName));
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = statusMailerBuildEmailTemplate($title, $intro, $rows, $footerNote);

        $altLines = [$title, '', $intro, ''];
        foreach ($rows as $label => $value) {
            $altLines[] = $label . ': ' . $value;
        }
        $altLines[] = '';
        $altLines[] = $footerNote;
        $mail->AltBody = implode("\n", $altLines);

        $mail->send();
        return true;
    } catch (MailerException $e) {
        error_log('Status mail error: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log('Status mail error: ' . $e->getMessage());
        return false;
    }
}

function sendAppointmentStatusEmail($toEmail, $toName, $appointmentId, $status, $appointmentDate, $appointmentTime)
{
    $statusText = ucfirst(strtolower(trim((string) $status)));
    $subject = "Appointment #{$appointmentId} - {$statusText}";
    $title = 'Your Appointment Status Has Been Updated';
    $intro = 'Your appointment request has been reviewed by our team. Please check the updated details below.';
    $rows = [
        'Customer' => $toName !== '' ? $toName : 'Valued Customer',
        'Appointment ID' => '#' . (int) $appointmentId,
        'Status' => $statusText,
        'Date' => $appointmentDate !== '' ? $appointmentDate : '-',
        'Time' => $appointmentTime !== '' ? $appointmentTime : '-',
    ];
    $footer = 'If you need help, please contact ClassyCut support or visit your dashboard notifications.';

    return statusMailerSend($toEmail, $toName, $subject, $title, $intro, $rows, $footer);
}

function sendOrderStatusEmail($toEmail, $toName, $orderId, $status, $totalAmount = null, $extraMessage = '')
{
    $statusText = ucfirst(strtolower(trim((string) $status)));
    $subject = "Order #{$orderId} - {$statusText}";
    $title = 'Your Order Status Has Been Updated';
    $intro = trim((string) $extraMessage);
    if ($intro === '') {
        $intro = 'We have updated your order progress. Please find the latest details below.';
    }

    $rows = [
        'Customer' => $toName !== '' ? $toName : 'Valued Customer',
        'Order ID' => '#' . (int) $orderId,
        'Status' => $statusText,
    ];
    if ($totalAmount !== null && is_numeric($totalAmount)) {
        $rows['Order Total'] = '₹' . number_format((float) $totalAmount, 2);
    }

    $footer = 'Thank you for choosing ClassyCut. You can track all updates in your account dashboard.';

    return statusMailerSend($toEmail, $toName, $subject, $title, $intro, $rows, $footer);
}

