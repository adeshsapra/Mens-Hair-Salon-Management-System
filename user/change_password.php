<?php
include 'connect.php';
include 'header.php'; 


// $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// $query = "SELECT * FROM user_reg WHERE id = $user_id";
// $user_data = $con->query($query);
// $user_row = mysqli_fetch_assoc($user_data);

if(isset($_POST['pass'])){
    $user_id = $_SESSION['user_id'];
    $query = "SELECT `password` FROM user_reg WHERE id = '$user_id'";
    $result = mysqli_query($con, $query);
    $user_row = mysqli_fetch_assoc($result);

    $old = $user_row['password']; 
    $hide_id_user_hide = $_POST['hide_id_user_hide'];
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if($old != $current_pass){
        $message[]='Incorrect Current Password..!';
    }
    else{
        if($new_pass !== $confirm_pass){
            $message[] = 'Confirm Password do not match..!';
        }
        else
        {
            $q=mysqli_query($con,"UPDATE user_reg SET `password`='$confirm_pass' WHERE `id`='$hide_id_user_hide'")
            or die('Qurey Failed');
            if($q)
            {
                $message[]='Password Changed Successfully';
            }
            else
            {
                $message[]='Password Not Changed..!';
            }
        }
    }
}
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Change Password</h1>
    </div>

    <div style="max-width: 600px; background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); padding: 32px; margin: 0 auto;">
        
        <?php
        if(isset($message) && is_array($message)) {
            foreach($message as $msg) {
                if ($msg == 'Password Changed Successfully') {
                    echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px;"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($msg) . '</div>';
                } else {
                    echo '<div style="background: #fce8e6; color: #d93025; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px;"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($msg) . '</div>';
                }
            }
        }
        if(isset($confirm) && is_array($confirm)) {
            foreach($confirm as $conf) {
                echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px;"><i class="fas fa-info-circle"></i> ' . htmlspecialchars($conf) . '</div>';
            }
        }
        ?>

        <form action="change_password.php" method="POST">
            <input type="hidden" name="hide_id_user_hide" value="<?php echo $_SESSION['user_id']; ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">Current Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                    <input type="password" name="current_password" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: 0.2s;" onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='#ddd'">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">New Password</label>
                <div style="position: relative;">
                    <i class="fas fa-key" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                    <input type="password" name="new_password" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: 0.2s;" onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='#ddd'">
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">Confirm New Password</label>
                <div style="position: relative;">
                    <i class="fas fa-check-double" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                    <input type="password" name="confirm_password" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: 0.2s;" onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='#ddd'">
                </div>
            </div>
            
            <button type="submit" name="pass" class="app_more" style="width: 100%; display: block; border: none; padding: 14px; font-size: 16px; border-radius: 8px; cursor: pointer; text-align: center;"><i class="fas fa-save"></i> Update Password</button>
        </form>
    </div>
</main>
