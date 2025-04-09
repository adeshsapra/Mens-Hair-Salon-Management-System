<?php 
include 'connect.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($newPassword === $confirmPassword) {
        $sql = "INSERT INTO admin (admin_email, admin_password, admin_name) VALUES ('$email', '$newPassword','$name')";
        if (mysqli_query($con, $sql)) {
            echo "New admin added successfully";
            header('Location: manage_admin.php');
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($con);
        }
    } else {
        echo "Passwords do not match.";
    }
}
?>

<div class="main-content">
    <div class="content">
        <h1>Add New Admin</h1>
        <form action="" method="post" style="text-transform:none;">
        <div class="form-group">
                <label for="name">Enter Name</label>
                <input type="text" id="name" name="name" required>
                <span class="error" id="emailError"></span>
            </div>
            <div class="form-group">
                <label for="email">Enter Email</label>
                <input type="email" id="email" name="email" required class="lowercase-input">
                <span class="error" id="emailError"></span>
            </div>
            <div class="form-group">
                <label for="newPassword">Enter Password</label>
                <input type="password" id="newPassword" name="newPassword" required>
                <span class="error" id="newPasswordError"></span>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <span class="error" id="confirmPasswordError"></span>
            </div>
            <button type="submit" name="add" class="btn">ADD</button>
        </form>
    </div>
</div>
