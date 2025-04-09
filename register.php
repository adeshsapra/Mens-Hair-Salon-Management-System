<?php

include('connect.php');

if(isset($_POST['sign-up'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_folder = 'upload_img/' . $image;

    $error = [];

    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Invalid Email Format!';
    } elseif (strpos($email, '@gmail.com') === false) {
        $error[] = 'Email must be a Gmail address!';
    }

    // Check for existing user
    $sel = mysqli_query($con, "SELECT * FROM user_reg WHERE username = '$username'") or die('Query Failed');
    if (mysqli_num_rows($sel) > 0) {
        $error[] = 'User Already Exists..!';
    }

    // Password Validation
    if ($password != $confirm_pass) {
        $error[] = 'Password Does Not Match..!';
    }

    // If no errors, proceed with registration
    if (empty($error)) {
        $insert = mysqli_query($con, "INSERT INTO user_reg (name, email, username, password, profile_img)
        VALUES ('$name', '$email', '$username', '$password', '$image')") or die('Query Failed');

        if ($insert) {
            move_uploaded_file($image_tmp, $image_folder);
            $error[] = 'Registration Successful..!';
            header('location:login.php');
        } else {
            $error[] = 'Unable to Create Account, Please Try Again..!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Register</title>
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
            <form id="register-form" class="form active" action="" method="POST" enctype="multipart/form-data">
                <h2>Create An Account</h2>
                <?php
                    if (isset($error)) {
                        foreach ($error as $error) {
                            echo '<div class="error">' . $error . '</div>';
                        }
                    }
                ?>
                <div class="signup-input">
                    <input type="text" name="name" placeholder="Enter Full Name" required>
                </div>
                <div class="signup-input">
                    <input type="email" name="email" placeholder="Enter Email" required>
                </div>
                <div class="signup-input">
                    <input type="text" name="username" placeholder="Enter Username" required>
                </div>
                <div class="signup-input">
                    <input type="password" id="password" name="password" placeholder="Create Password" required>
                </div>
                <div class="signup-input">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="show-password">
                    <input type="checkbox" id="show-password-checkbox">
                    <label for="show-password-checkbox">Show Password</label>
                </div>
                <div class="signup-input">
                    <label for="profile">* Choose Profile Image:</label>
                    <input type="file" name="image" id="file" accept=".jpg,.jpeg,.png">
                </div>
                <button type="submit" name="sign-up">Sign Up</button>
                <p class="link">Already have an account? <a href="login.php" id="sign-in-link">Sign In</a></p>
            </form>
        </div>
    </div>

    <script>
        const showPasswordCheckbox = document.getElementById('show-password-checkbox');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        showPasswordCheckbox.addEventListener('change', function() {
            const type = this.checked ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            confirmPasswordInput.setAttribute('type', type);
        });
    </script>
</body>
</html>