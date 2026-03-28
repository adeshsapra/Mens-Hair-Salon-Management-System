<?php
ob_start();
include 'connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
        <li><a href="database_backup.php"><i class="fas fa-database"></i> Database Backup</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<!-- ========================================== -->
<!-- GLOBAL TOAST & MODAL SYSTEM (Injected) -->
<!-- ========================================== -->
<style>
/* Global Custom Confirm Modal */
#global-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    z-index: 99999; display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0; transition: all 0.3s ease;
}
#global-confirm-overlay.show {
    visibility: visible; opacity: 1;
}
.global-confirm-box {
    background: #fff; padding: 30px; border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 350px; text-align: center;
    transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
#global-confirm-overlay.show .global-confirm-box {
    transform: translateY(0) scale(1);
}
.global-confirm-icon {
    font-size: 40px; color: #f59e0b; margin-bottom: 15px;
}
.global-confirm-text {
    font-size: 18px; color: #333; margin-bottom: 25px; font-weight: 500;
}
.global-confirm-actions {
    display: flex; gap: 15px; justify-content: center;
}
.global-confirm-btn {
    padding: 10px 20px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; font-weight: 600; transition: all 0.2s;
}
.gc-cancel { background: #e2e8f0; color: #475569; }
.gc-cancel:hover { background: #cbd5e1; }
.gc-confirm { background: #ef4444; color: #fff; }
.gc-confirm:hover { background: #dc2626; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); }

/* Global Toast System */
#global-toast-container {
    position: fixed; bottom: 20px; right: 20px; z-index: 100000;
    display: flex; flex-direction: column; gap: 10px;
}
.global-toast {
    min-width: 250px; background: #333; color: #fff; padding: 15px 20px;
    border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500;
    transform: translateX(120%); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.global-toast.show {
    transform: translateX(0);
}
.toast-success { background: #10b981; border-left: 5px solid #059669; }
.toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
.toast-info { background: #3b82f6; border-left: 5px solid #2563eb; }
</style>

<!-- HTML Elements -->
<div id="global-confirm-overlay">
    <div class="global-confirm-box">
        <div class="global-confirm-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="global-confirm-text" id="global-confirm-msg">Are you sure?</div>
        <div class="global-confirm-actions">
            <button class="global-confirm-btn gc-cancel" id="gc-cancel-btn">Cancel</button>
            <button class="global-confirm-btn gc-confirm" id="gc-confirm-btn">Yes, I'm sure</button>
        </div>
    </div>
</div>
<div id="global-toast-container"></div>

<script>
// --- TOAST NOTIFICATIONS ---
function showToast(message, type = 'success') {
    const container = document.getElementById('global-toast-container');
    const toast = document.createElement('div');
    toast.className = `global-toast toast-${type}`;
    
    let icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3.5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400); // Wait for transition
    }, 3500);
}

// Convert existing PHP static messages to toasts!
document.addEventListener('DOMContentLoaded', () => {
    // Find all legacy messages
    const phpAlerts = document.querySelectorAll('.message, .success, .confirm, .error');
    phpAlerts.forEach(alert => {
        let text = alert.innerText.trim();
        if(text) {
            let type = (alert.classList.contains('message') || alert.classList.contains('error')) ? 'error' : 'success';
            showToast(text, type);
        }
        alert.style.display = 'none'; // Hide the ugly static box
    });
    
    // Check for PHP Session toasts (Legacy fail-safe)
    <?php if (isset($_SESSION['toast-msg'])): ?>
        showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo isset($_SESSION['toast-type']) ? $_SESSION['toast-type'] : 'success'; ?>");
        <?php 
            unset($_SESSION['toast-msg']);
            unset($_SESSION['toast-type']);
        ?>
    <?php endif; ?>

    // Check for URL Parameter toasts (Ultra reliable)
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('toast')) {
        let msg = urlParams.get('msg') || 'Action completed successfully!';
        let type = urlParams.get('toast') || 'success';
        showToast(msg, type);
        
        // Clean the URL so refreshing doesn't replay the toast
        window.history.replaceState(null, '', window.location.pathname);
    }
});

// --- CUSTOM CONFIRM MODAL ---
let confirmCallback = null;
const confirmOverlay = document.getElementById('global-confirm-overlay');
const confirmMsgElem = document.getElementById('global-confirm-msg');
const btnConfirm = document.getElementById('gc-confirm-btn');
const btnCancel = document.getElementById('gc-cancel-btn');

function openGlobalConfirm(msg, callback) {
    confirmMsgElem.innerText = msg;
    confirmCallback = callback;
    confirmOverlay.classList.add('show');
}

function closeGlobalConfirm() {
    confirmOverlay.classList.remove('show');
    confirmCallback = null;
}

btnCancel.onclick = closeGlobalConfirm;
btnConfirm.onclick = () => {
    if(confirmCallback) confirmCallback();
    closeGlobalConfirm();
};

// Global intercepter for all onclick="return confirm(...)"
document.addEventListener('click', function(e) {
    let el = e.target.closest('[onclick*="confirm("]');
    if (el) {
        let onclickStr = el.getAttribute('onclick');
        // Extract message from confirm('Message')
        let match = onclickStr.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            let msg = match[1];
            
            openGlobalConfirm(msg, () => {
                // If user clicks yes, execute the action without the confirm blocker
                el.removeAttribute('onclick'); // prevent loop
                if (el.tagName === 'A' && el.href) {
                    window.location.href = el.href;
                } else {
                    el.click(); // re-trigger
                }
            });
        }
    }
}, true); // Important: capture phase to stop immediate propagation before inline handler
</script>
