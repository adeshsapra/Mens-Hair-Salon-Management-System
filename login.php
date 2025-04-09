<?php

include 'connect.php';

if(isset($_POST['sign-in'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error[] = 'Please enter both username and password.';
    } else {
        if ($username === 'admin' && $password === '123') {
            header('Location: admin/index.php');
            exit();
        } else {
            $sel = mysqli_query($con, "SELECT * FROM user_reg WHERE username = '$username' AND password = '$password'") or die('Query Failed');

            if (mysqli_num_rows($sel) > 0) {
                $row = mysqli_fetch_assoc($sel);
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['profile_image'] = $row['profile_img'];
                header('Location: index.php');
                exit();
            } else {
                $error[] = 'Incorrect Username or Password.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="log-reg-body">
    <div class="log-reg-container">
        <div class="image-container">
            <img src="photos/skill.jpeg" alt="Salon Image">
            <div class="image-text">
                <h2>ClassyCut</h2>
            </div>
        </div>
        <div class="form-container">
            <form id="login-form" class="form active" action="" method="POST" enctype="multipart/form-data">
                <h2>Login</h2>
                <?php
                    if(isset($error)) {
                        foreach($error as $error) {
                            echo '<div class="error">' . $error . '</div>';
                        }
                    }
                ?>
                <div class="signin-input">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="signin-input">
                    <i class="fas fa-lock" id="toggleLock" style="cursor: pointer;"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" name="sign-in" style="margin:0;">Sign In</button>
                <p class="link">New In ClassyCut? <a href="register.php" id="sign-up-link">Sign Up</a></p>
            </form>
        </div>
    </div>

    <!-- JavaScript for Show/Hide Password -->
    <script>
        const toggleLock = document.querySelector('#toggleLock');
        const passwordField = document.querySelector('#password');

        toggleLock.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-lock');
            this.classList.toggle('fa-lock-open');
        });
    </script>
</body>
</html>
