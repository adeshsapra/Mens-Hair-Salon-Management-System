<?php

include 'connect.php';

if(isset($_POST['sign-in'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error[] = 'Please enter both username and password.';
    } else {
        if ($username === 'admin' && $password === '123') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_id'] = 'admin'; // Static admin ID for the demo/simple setup
            $_SESSION['toast-msg'] = 'Admin Login Successful! Welcome to the dashboard.';
            $_SESSION['toast-type'] = 'info';
            header('Location: admin/index.php');
            exit();
        } else {
            $sel = mysqli_query($con, "SELECT * FROM user_reg WHERE username = '$username' AND password = '$password'") or die('Query Failed');

            if (mysqli_num_rows($sel) > 0) {
                $row = mysqli_fetch_assoc($sel);
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['profile_image'] = $row['profile_img'];
                $_SESSION['toast-msg'] = 'Login Successful! Welcome, ' . $row['username'] . '.';
                $_SESSION['toast-type'] = 'success';
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
    <!-- Toast System -->
    <style>
        #global-toast-container {
            position: fixed; bottom: 20px; right: 20px; z-index: 100000;
            display: flex; flex-direction: column; gap: 10px;
        }
        .global-toast {
            min-width: 250px; background: #333; color: #fff; padding: 15px 20px;
            border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500;
            transform: translateX(120%); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .global-toast.show { transform: translateX(0); }
        .toast-success { background: #10b981; border-left: 5px solid #059669; }
        .toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
        .toast-info { background: #3b82f6; border-left: 5px solid #2563eb; }
    </style>
    <div id="global-toast-container"></div>
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('global-toast-container');
            const toast = document.createElement('div');
            toast.className = `global-toast toast-${type}`;
            let icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
            toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Check for PHP Session toasts
            <?php
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['toast-msg'])): ?>
                showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo isset($_SESSION['toast-type']) ? $_SESSION['toast-type'] : 'success'; ?>");
                <?php
                unset($_SESSION['toast-msg']);
                unset($_SESSION['toast-type']);
            endif;
            ?>

            // Convert PHP static messages to toasts
            document.querySelectorAll('.error, .success, .message').forEach(alert => {
                let text = alert.innerText.trim();
                if(text) {
                    let type = alert.classList.contains('error') ? 'error' : 'success';
                    showToast(text, type);
                }
                alert.style.display = 'none'; // Hide the static element
            });
        });
    </script>
</body>
</html>
