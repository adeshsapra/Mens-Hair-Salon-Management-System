<?php
if (!isset($con)) {
    include_once __DIR__ . '/connect.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
    <!-- header and navigation section -->

    <header class="header">

        <a href="index.php" class="logo">
            <img src="photos/logoo.png" alt="ClassyCut">
        </a>
        <nav class="menu">
            <a href="index.php">Home</a>
            <a href="service.php">Services</a>
            <a href="eshop.php">E-shop</a>
            <a href="membership.php">Membership</a>
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

            $username = ($row && isset($row['username'])) ? $row['username'] : 'Member';
            echo '<div class="user-profile">';
            echo '<a href="user/index.php"><i class="fas fa-user-circle"></i></a>';
            echo '<a href="user/index.php" class="username">' . htmlspecialchars($username) . '</a>';
            echo '</div>';
        } else {
            echo '<div class="login">';
            echo '<a href="login.php">Sign-In</a>';
            echo '</div>';
        }
        ?>
    </header>
    <script src="js/script.js"></script>
