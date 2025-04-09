<?php
session_start();
if (isset($_POST['verify-otp'])) {
    $entered_otp = $_POST['otp'];
    if ($entered_otp == $_SESSION['otp']) {
        header('Location: reset_password.php');
        exit();
    } else {
        $error[] = 'Invalid OTP. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="log-reg-body">
    <div class="log-reg-container">
        <div class="form-container">
            <form id="verify-otp-form" class="form active" action="" method="POST">
                <h2>Verify OTP</h2>
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<div class="error">' . $error . '</div>';
                    }
                }
                ?>
                <div class="signin-input">
                    <i class="fas fa-key"></i>
                    <input type="text" name="otp" placeholder="Enter OTP" required>
                </div>
                <button type="submit" name="verify-otp">Verify OTP</button>
                <p class="link"><a href="forgot_password.php">Resend OTP</a></p>
            </form>
        </div>
    </div>
</body>
</html>