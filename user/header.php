<?php

include 'connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT profile_img FROM user_reg WHERE id = '$user_id'";
    $result = mysqli_query($con, $query);

    if ($result) {
        $user_row = mysqli_fetch_assoc($result);
        $profile_image = $user_row['profile_img'];
        if (empty($profile_image)) {
            $profile_image = '../upload_img/default.jpeg';
        }
    }
    else {
        $profile_image = '../upload_img/default.jpeg';
    }
}
else {
    $profile_image = '../upload_img/default.jpeg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon User Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="user.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        
        <!-- Mobile Topbar for Hamburger Menu -->
        <div class="mobile-topbar">
            <div class="mobile-logo">
                <img src="../upload_img/<?php echo $profile_image; ?>" alt="Salon Logo">
            </div>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Overlay for closing sidebar on mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <img src="../upload_img/<?php echo $profile_image; ?>" alt="Salon Logo">
            </div>
            <nav>
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <ul>
                    <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="appointment_user.php" class="<?= ($current_page == 'appointment_user.php') ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                    <li>
                        <a href="#" id="productMenu" class="<?= in_array($current_page, ['products_user.php', 'order.php']) ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a>
                        <ul id="productSubmenu" class="submenu <?= in_array($current_page, ['products_user.php', 'order.php']) ? 'open' : '' ?>">
                            <li><a href="products_user.php" class="<?= ($current_page == 'products_user.php') ? 'active' : '' ?>"><i class="fas fa-cart-plus"></i> Add to Cart</a></li>
                            <li><a href="order.php" class="<?= ($current_page == 'order.php') ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> My Orders</a></li>
                        </ul>
                    </li>
                    <li><a href="membership_user.php" class="<?= ($current_page == 'membership_user.php') ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Membership</a></li>
                    <li><a href="payment_user.php" class="<?= ($current_page == 'payment_user.php' || $current_page == 'checkout.php') ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> Payments</a></li>
                    <li><a href="user_wallet.php" class="<?= ($current_page == 'user_wallet.php') ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Wallet</a></li>
                    <li><a href="settings.php" class="<?= ($current_page == 'settings.php' || $current_page == 'edit_profile.php' || $current_page == 'change_password.php') ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> User Settings</a></li>
                    <li><a href="user_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

    <script>
        // Product Submenu Toggle
        document.getElementById('productMenu').addEventListener('click', function(event) {
            event.preventDefault(); 
            var submenu = document.getElementById('productSubmenu');
            submenu.classList.toggle('open');
        });

        // Mobile Sidebar Toggle Logic
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>