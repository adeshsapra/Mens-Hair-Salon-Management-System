<?php
include 'connect.php';
include 'header.php'; 

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_reg WHERE id = '$user_id'";
$user_data = $con->query($query);
$user_row = mysqli_fetch_assoc($user_data);

// Existing logic for updating...
if (isset($_POST['user-update'])) {
    $update_id = $_SESSION['user_id'];
    $update_parts = [];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $u_image=$_FILES['profile_photo']['name'];
        $u_image_tmp=$_FILES['profile_photo']['tmp_name'];
        $u_image_folder='../upload_img/'.$u_image;
        
        if (move_uploaded_file($u_image_tmp,$u_image_folder)) {
            // Keep just the filename or relative path
            $update_parts[] = "profile_img='$u_image'";
        }
    }

    if (!empty($_POST['name'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $update_parts[] = "name='$name'";
    }

    if (!empty($_POST['username'])) {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $update_parts[] = "username='$username'";
    }
    
    if (!empty($_POST['email'])) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $update_parts[] = "email='$email'";
    }
    
    if (!empty($update_parts)) {
        $update_query = "UPDATE user_reg SET " . implode(', ', $update_parts) . " WHERE id='$update_id'";
        
        if ($con->query($update_query) === TRUE) {
            header('Location:settings.php?toast=success&msg=Profile+updated+successfully!'); exit;
        } else {
            $error = "Error updating: " . $con->error;
        }
    }
}
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Edit Profile</h1>
        <a href="settings.php" class="app_more" style="margin-top: 0; background: #666;"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div style="max-width: 700px; background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); padding: 32px; margin: 0 auto;">
        
        <?php if(isset($error)): ?>
            <div style="background: #fce8e6; color: #d93025; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">

            <!-- Profile Photo Section -->
            <div style="text-align: center; margin-bottom: 32px; position: relative;">
                <div style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 16px; border: 4px solid var(--brand); padding: 3px; background: white; overflow: hidden; box-shadow: var(--shadow-md);">
                    <?php 
                        $img_path = $user_row['profile_img'];
                        if(strpos($img_path, '../') === false) {
                            $img_src = '../upload_img/' . $img_path;
                        } else {
                            $img_src = $img_path;
                        }
                    ?>
                    <img id="preview" src="<?php echo $img_src; ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
                <label for="profile_photo" style="background: var(--bg1); color: var(--brand); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-block; transition: 0.2s;">
                    <i class="fas fa-camera"></i> Change Photo
                </label>
                <input type="file" id="profile_photo" name="profile_photo" accept=".jpg, .jpeg, .png" style="display: none;" onchange="previewImage(this)">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">Full Name</label>
                    <div style="position: relative;">
                        <i class="fas fa-user" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user_row['name']); ?>" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">Username</label>
                    <div style="position: relative;">
                        <i class="fas fa-at" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user_row['username']); ?>" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; color: var(--bg1); margin-bottom: 8px;">Email Address</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope" style="position: absolute; left: 14px; top: 14px; color: #999;"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_row['email']); ?>" required style="width: 100%; padding: 12px 16px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none;">
                </div>
            </div>

            <button type="submit" name="user-update" class="app_more" style="width: 100%; display: block; border: none; padding: 16px; font-size: 16px; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600;">
                <i class="fas fa-user-check"></i> Save Profile Changes
            </button>
        </form>
    </div>
</main>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

