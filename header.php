<?php
    include('connect.php');
     session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut Salon</title>

    <!-- font awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
     <!-- box link -->
     <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <!-- header and navigation section -->

    <header class="header">

        <a href="#" class="logo">
            <img src="photos/logoo.png" alt="">
        </a>
        <nav class="menu">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="service.php">Services</a>
            <a href="eshop.php">E-shop</a>
            <a href="membership.php">Membership</a>
            <a href="contact.php">Contact</a>
            <!-- <a href="appointment.php">Appointment</a> -->
            <?php
            
            if (isset($_SESSION['user_id'])) {
                echo '<a href="appointment.php">Appointment</a>';
            }
        ?>
        </nav>
        <div class="icons">
             <div class="fas fa-search" id="search-btn"></div>
             <div class="fas fa-bars" id="menu-btn"></div>
        </div>
        <div class="search-form">
            <input type="search" name="search" id="search-box" placeholder="Search Here....">
            <label for="search-box" class="fas fa-search"></label>
        </div>
        <?php
        
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $query = "SELECT username FROM user_reg WHERE id = '$user_id'";
                $result = mysqli_query($con, $query);
                $row = mysqli_fetch_assoc($result);
    
                $username = $row['username'];
                echo '<div class="user-profile">';
                echo '<a href="user/index.php"><i class="fas fa-user-circle"></i></a>'; 
                echo '<a href="user/index.php" class="username">' . $username . '</a>';
                echo '</div>';
            } else {
                echo '<div class="login">';
                echo '<a href="login.php">Sign-In</a>';
                echo '</div>';
            }
        ?>
        <!-- <div class="login">
            <a href="login.php">Sign-In</a>
        </div> -->
    </header>
    <script src="js/script.js"></script>
</body>
</html>