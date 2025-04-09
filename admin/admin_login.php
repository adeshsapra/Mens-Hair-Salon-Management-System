<?php

include 'connect.php';


if(isset($_POST['admin-sign-in'])){

    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];

    if (empty($admin_email) || empty($admin_password)) {
        $error[] = 'Please enter both email and password.';
    } else {
        // Admin credentials validation (hardcoded example or check from database)
        $sel = mysqli_query($con, "SELECT * FROM admin WHERE admin_email = '$admin_email' AND admin_password = '$admin_password'") 
        or die('Query Failed');
        if (mysqli_num_rows($sel) > 0) {
            $row = mysqli_fetch_assoc($sel);
            session_start();
            $_SESSION['admin_id'] = $row['admin_id'];
            header('Location:index.php');
            exit();
        } else {
            $error[] = 'Incorrect Admin Email or Password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="log-reg-body">
    <div class="log-reg-container">
        <div class="image-container">
            <img src="../photos/skill.jpeg" alt="Admin Image">
            <div class="image-text">
                <h2>Classycut Admin Panel</h2>
            </div>
        </div>
        <div class="form-container">
            <form id="admin-login-form" class="form active" action="" method="POST">
                <h2>Admin Login</h2>
                <?php
                    if(isset($error))
                    {
                        foreach($error as $error)
                        {
                            echo '<div class="error">' . $error . '</div>';
                        }
                    }
                ?>
                <div class="signin-input">
                    <i class="fas fa-user"></i>
                    <input type="email" name="admin_email" placeholder="E-Mail" required>
                </div>
                <div class="signin-input">
                    <i class="fas fa-lock" id="toggleLock" style="cursor: pointer;"></i>
                    <input type="password" id="admin_password" name="admin_password" placeholder="Password" required>
                </div>
                <button type="submit" name="admin-sign-in" style="margin:0;">Sign In</button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Show/Hide Password -->
    <script>
        const toggleLock = document.querySelector('#toggleLock');
        const passwordField = document.querySelector('#admin_password');

        toggleLock.addEventListener('click', function (e) {
            // Toggle the type attribute between password and text
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle the lock icon between locked (fa-lock) and unlocked (fa-lock-open)
            this.classList.toggle('fa-lock');
            this.classList.toggle('fa-lock-open');
        });
    </script>
</body>
</html>