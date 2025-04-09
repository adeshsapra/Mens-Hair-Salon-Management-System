<?php 
include 'connect.php';
include 'header.php'; 

?>
<main class="content">       
    <section class="settings">
        <div class="setting">
            <h2>Profile</h2>
            <p>Name: <?php echo $_SESSION['username']; ?></p>
            <p>Email: <?php echo $_SESSION['email']; ?></p>
            <a href="edit_profile.php?id=<?php echo $_SESSION["user_id"]; ?>">
                <button class="update">Edit Profile</button>
            </a>
        </div>
        <div class="setting">
            <h2>Change Password</h2>
            <a href="change_password.php?id=<?php echo $_SESSION["user_id"]; ?>" class="button">Change Password</a>
        </div>
    </section>
</main>
