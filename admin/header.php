<?php

include 'connect.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT admin_name FROM admin WHERE admin_id = $admin_id";
$admin_result = mysqli_query($con, $admin_query);

$adminName = 'Admin';

if ($admin_result) {
    if ($row = mysqli_fetch_assoc($admin_result)) {
        $adminName = $row['admin_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Management Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        ul {
            list-style-type: none;
             margin: 0;
             padding: 0;
            }
        .submenu {
            display: none;
            /* background: #34495e; */
            padding-left: 18px;
            padding-right:10px;
        }
        .submenu a {
            padding: 10px 15px;
        }
        .submenu a:hover {
            /* background: #3a9bdc; */
            background:#eae3c2;
            border-radius:10px;
        }
        .active .submenu {
            display: block;
        }
    </style>
    <script>
        function toggleSubmenu(event) {
            // Prevent default action of anchor
            event.preventDefault();
            const parentLi = event.currentTarget.parentElement;

            // Close any open submenus
            const allSubmenus = document.querySelectorAll('.submenu');
            allSubmenus.forEach(submenu => {
                if (submenu !== parentLi.querySelector('.submenu')) {
                    submenu.style.display = 'none';
                    submenu.parentElement.classList.remove('active');
                }
            });
            const submenu = parentLi.querySelector('.submenu');
            if (submenu) {
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                parentLi.classList.toggle('active');
            }
        }
    </script>
</head>
<body>

<div class="header">
    <div class="user-profile">
        <div class="dropdown">
            <button class="dropbtn"><i class="fas fa-user-circle" id="admin-icon"></i>Admin</button>
            <div class="dropdown-content">
            <p style="color:black;padding:10px;padding-left:20px;">Name: <?php echo $adminName ?></p>                
            <a href="manage_admin.php">Manage Admin</a>
                <a href="change_password.php">Change Password</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="sidebar">
    <div class="logo">
        <h2>Classycut Salon</h2>
    </div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="customer.php"><i class="fas fa-user"></i> Clients</a></li>
        <li><a href="appointments_manage.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
        <li>
            <a href="products.php" onclick="toggleSubmenu(event)"><i class="fas fa-box"></i> Products</a>
            <ul class="submenu">
                <li><a href="products.php"><i class="fas fa-cogs"></i> Manage Products</a></li>
                <li><a href="manage_orders.php"><i class="fas fa-receipt"></i> Manage Orders</a></li>
            </ul>
        </li>
        <li>
            <a href="membership_manage.php" onclick="toggleSubmenu(event)"><i class="fas fa-box"></i> Membership</a>
            <ul class="submenu">
                <li><a href="membership_manage.php"><i class="fas fa-cogs"></i> Manage Membership</a></li>
                <li><a href="membership_details.php"><i class="fas fa-user-tag"></i> Membership Details</a></li>

            </ul>
        </li>
        <li><a href="service_manage.php"><i class="fas fa-cut"></i> Services</a></li>
        <li><a href="payment_manage.php"><i class="fas fa-box"></i> Payment</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
