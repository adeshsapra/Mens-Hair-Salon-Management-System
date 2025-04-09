<?php
include 'connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'asset/PHPMailer.php';
require 'asset/SMTP.php';
require 'asset/Exception.php';

session_start();
if (isset($_POST['send-otp'])) {
    $email = $_POST['email'];
    if (empty($email)) {
        $error[] = 'Please enter your email.';
    } else {
        $sel = mysqli_query($con, "SELECT name FROM user_reg WHERE email = '$email'") or die('Query Failed');
        
        if (mysqli_num_rows($sel) > 0) {
            $user = mysqli_fetch_assoc($sel);
            $userName = $user['name'];
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email'] = $email;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'classycut007@gmail.com';
                $mail->Password   = 'dgjg qxjo icve bita'; // Ensure this is secured
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('classycut007@gmail.com', 'Classycut');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP from ClassyCut';
                $mail->Body    = "
                Dear $userName,<br><br>
                We received a request to reset your password for your ClassyCut account. To proceed, please enter the OTP (One-Time Password) below:<br><br>
                Your OTP: <strong>$otp</strong><br>
                Thank you for using ClassyCut!<br>
                Best regards,<br>
                <b><i>The ClassyCut Team</i></b>";
                $mail->AltBody = "Dear $userName,\n\nYour OTP is: $otp";

                $mail->send();
                
                $_SESSION['otp_sent'] = true;
                header('Location: forgot_password.php'); // Redirect back to this page
                exit();
            } catch (Exception $e) {
                $error[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error[] = 'Email not found.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Pop-up Styles */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            border-radius: 8px;
            text-align: center;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(5px);
            z-index: 999;
        }

        .popup i {
            margin-bottom: 10px;
            color: green; 
            font-size: 50px;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body class="log-reg-body">
    <div class="log-reg-container">
        <div class="form-container">
            <form id="forgot-password-form" class="form active" action="" method="POST">
                <h2>Forgot Password</h2>
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<div class="error">' . $error . '</div>';
                    }
                }
                ?>
                <div class="signin-input">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <button type="submit" name="send-otp">Send OTP</button>
                <p class="link">Remembered your password? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>

    <!-- Popup section -->
    <div class="popup-overlay" id="popup-overlay" style="display: none;"></div>
    <div class="popup" id="popup" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <p style="font-size: 18px; margin-top: 10px;">OTP sent successfully!</p>
    </div>

    <script>
        window.onload = function() {
            <?php if (isset($_SESSION['otp_sent'])): ?>
                document.getElementById('popup').style.display = 'block';
                document.getElementById('popup-overlay').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('popup').style.display = 'none';
                    document.getElementById('popup-overlay').style.display = 'none';
                    <?php unset($_SESSION['otp_sent']); ?> 
                    window.location.href = 'verify_otp.php'; 
                }, 2000); 
            <?php endif; ?>
        }
    </script>
</body>
</html>