<?php 
include 'connect.php';
session_start();

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
    } else {
        $profile_image = '../upload_img/default.jpeg';
    }
} else {
    $profile_image = '../upload_img/default.jpeg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <style>
        .submenu {
            display: none;
            list-style-type: none;
            padding-left: 20px;
        }
        .submenu.open {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../upload_img/<?php echo $profile_image;?>" alt="Salon Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="appointment_user.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                    <li>
                        <a href="#" id="productMenu"><i class="fas fa-box"></i> Products</a>
                        <ul id="productSubmenu" class="submenu">
                            <li><a href="products_user.php"><i class="fas fa-cart-plus"></i> Add to Cart</a></li>
                            <li><a href="order.php"><i class="fas fa-clipboard-list"></i>My Orders</a></li>
                        </ul>
                    </li>
                    <li><a href="membership_user.php"><i class="fas fa-cogs"></i> Membership</a></li>
                    <li><a href="payment_user.php"><i class="fas fa-user-cog"></i> Payments</a></li>
                    <li><a href="user_wallet.php"><i class="fas fa-wallet"></i>Wallet</a></li>
                    <li><a href="settings.php"><i class="fas fa-user-cog"></i> User Settings</a></li>
                    <li><a href="user_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

    <script>
        document.getElementById('productMenu').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default action of the link
            
            // Toggle submenu visibility
            var submenu = document.getElementById('productSubmenu');
            submenu.classList.toggle('open');
        });
    </script>