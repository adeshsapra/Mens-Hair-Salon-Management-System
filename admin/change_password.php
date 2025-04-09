<?php
include 'connect.php';
include 'header.php'; 

if(isset($_POST['pass'])){
    $admin_id = $_SESSION['admin_id'];
    $query = "SELECT `admin_password` FROM admin WHERE admin_id = '$admin_id'";
    $result = mysqli_query($con, $query);
    $admin_row = mysqli_fetch_assoc($result);

    $old_password = $admin_row['admin_password']; 
    $hide_id_admin_hide = $_POST['hide_id_admin_hide'];
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if($old_password != $current_pass){
        $message[] = 'Incorrect Current Password!';
    } else {
        if($new_pass !== $confirm_pass){
            $message[] = 'Confirm Password does not match!';
        } else {
            // Update password query for admin
            $q = mysqli_query($con, "UPDATE admin SET `admin_password`='$confirm_pass' WHERE `admin_id`='$hide_id_admin_hide'")
            or die('Query Failed');
            if($q) {
                $message[] = 'Password Changed Successfully';
            } else {
                $message[] = 'Password Not Changed!';
            }
        }
    }
}
?>

<div class="main-content">
    <div class="content">
        <h1>Change Password</h1>

        <?php
        if(isset($message)){
            foreach($message as $msg){
                echo '<div class="message">'.$msg.'</div>';
            }
        }
        ?>
    <form action="change_password.php" method="POST">
            <!-- Admin ID hidden field -->
            <input type="hidden" name="hide_id_admin_hide" value="<?php echo $_SESSION['admin_id']; ?>">
            
            <div class="form-group">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn"name="pass">Change Password</button>
        </form>
    </div>
</div> 
