<?php
include 'connect.php';
session_start();

if (isset($_POST['reset-password'])) {
    $new_password = $_POST['new_password'];
    $email = $_SESSION['email'];
    
    if (empty($new_password)) {
        $error[] = 'Please enter a new password.';
    } else {
        // Update password in the database
        mysqli_query($con, "UPDATE user_reg SET password = '$new_password' WHERE email = '$email'") or die('Query Failed');
        
        // Clear OTP session
        unset($_SESSION['otp']);
        unset($_SESSION['email']);
        
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="log-reg-body">
    <div class="log-reg-container">
        <div class="form-container">
            <form id="reset-password-form" class="form active" action="" method="POST">
                <h2>Reset Password</h2>
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<div class="error">' . $error . '</div>';
                    }
                }
                ?>
                <div class="signin-input">
                    <i class="fas fa-lock" id="toggleLock" style="cursor: pointer;"></i>
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>
                <button type="submit" name="reset-password">Reset Password</button>
                <p class="link"><a href="login.php">Go to Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>