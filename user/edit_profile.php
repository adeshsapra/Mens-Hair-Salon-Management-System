<?php
include 'connect.php';
include 'header.php'; 


$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM user_reg WHERE id = $user_id";
$user_data = $con->query($query);
$user_row = mysqli_fetch_assoc($user_data);

if (isset($_POST['user-update'])) {
    $update_id = intval($_POST['user_id']);
    $update_parts = [];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        
        $u_image=$_FILES['profile_photo']['name'];
        $u_image_size=$_FILES['profile_photo']['size'];
        $u_image_tmp=$_FILES['profile_photo']['tmp_name'];
        $u_image_folder='../upload_img/'.$u_image;
        
        if (move_uploaded_file($u_image_tmp,$u_image_folder)) {
            $update_parts[] = "profile_img='$u_image_folder'";
        }
    }

    if (!empty($_POST['name'])) {
        $name = $_POST['name'];
        $update_parts[] = "name='$name'";
    }

    if (!empty($_POST['username'])) {
        $username = $_POST['username'];
        $update_parts[] = "username='$username'";
    }
    
    if (!empty($_POST['email'])) {
        $email = $_POST['email'];
        $update_parts[] = "email='$email'";
    }
    
    if (!empty($update_parts)) {
        $update_query = "UPDATE user_reg SET " . implode(', ', $update_parts) . " WHERE id=$update_id";
        
        if ($con->query($update_query) === TRUE) {
            // $confirm[]= "Product updated successfully!";
            header("Location:settings.php");
        } else {
            echo "Error updating: " . $con->error;
        }
    } else {
        echo "No fields to update.";
    }
} else {
   // echo "No data submitted.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="content">
        <h2>Edit Profile</h2>
        
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Profile Photo:</label><br>
                <img src="../upload_img/<?php echo $user_row['profile_img']; ?>" alt="Profile Photo" width="100"><br><br>
                <input type="file" name="profile_photo"  accept=".jpg .jpeg .png">
            </div>
            
            <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">

            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo $user_row['name']; ?>">
            </div>

            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="<?php echo $user_row['username']; ?>">
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $user_row['email']; ?>">
            </div>

            <button type="submit" name="user-update">Update Profile</button>
        </form>
    </main>
</body>
</html>
