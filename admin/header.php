<?php
ob_start();
include 'connect.php';
require_once __DIR__ . '/../notification_helpers.php';
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

$admin_notification_unread = 0;
$admin_notification_items = [];
if ($admin_id > 0) {
    $admin_notification_unread = notificationGetUnreadCount($con, 'admin', $admin_id);
    $admin_notification_items = notificationGetRecent($con, 'admin', $admin_id, 4);
}

if (!function_exists('renderAdminNotificationPreview')) {
    function renderAdminNotificationPreview($items)
    {
        if (empty($items)) {
            echo '<div class="admin-notif-empty">No notifications yet.</div>';
            return;
        }

        foreach ($items as $item) {
            $isUnread = (int) ($item['is_read'] ?? 0) === 0;
            $itemClass = $isUnread ? 'admin-notif-item unread' : 'admin-notif-item';
            $href = notificationResolveLink($item['link_url'] ?? '');
            echo '<a class="' . $itemClass . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
            echo '<div class="admin-notif-row">';
            echo '<h5>' . htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') . '</h5>';
            echo '<span>' . htmlspecialchars(notificationFormatTimeAgo($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') . '</span>';
            echo '</div>';
            echo '<p>' . htmlspecialchars((string) ($item['message'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
            echo '</a>';
        }
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
        .has-submenu {
            justify-content: space-between;
        }
        .has-submenu .menu-label {
            display: inline-flex;
            align-items: center;
        }
        .submenu-caret {
            margin-right: 0 !important;
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.2s ease;
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
        .active > .has-submenu .submenu-caret {
            transform: rotate(180deg);
        }
        .header .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-notif-wrap {
            position: relative;
            margin-right: 0;
            z-index: 1300;
            display: inline-flex;
            align-items: center;
        }
        .admin-notif-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 0;
            background: #f2eecf;
            color: #18150d;
            font-size: 17px;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .admin-notif-btn i {
            color: #18150d !important;
            margin: 0 !important;
        }
        .admin-profile-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            z-index: 1350;
        }
        .admin-profile-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--bg2);
            color: var(--bg1);
            border: none;
            margin: 0 2rem 0 0;
            padding: 0;
            cursor: pointer;
            font-size: 25px;
            font-weight: 600;
            line-height: 1;
            text-transform: none;
        }
        .admin-profile-btn i {
            margin: 0 !important;
            color: var(--bg1) !important;
        }
        .admin-profile-menu {
            display: block;
            position: absolute;
            top: 54px;
            right: 0;
            min-width: 220px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.2s ease;
        }
        .admin-profile-wrap:hover .admin-profile-menu,
        .admin-profile-wrap.open .admin-profile-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .admin-profile-head {
            margin: 0;
            padding: 12px 14px;
            color: #1d1d1d;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #ececec;
            background: #faf7e9;
            text-transform: none;
        }
        .admin-profile-menu a {
            display: block;
            padding: 11px 14px;
            color: #1d1d1d;
            font-size: 15px;
            font-weight: 500;
            text-transform: none;
        }
        .admin-profile-menu a:hover {
            background: #f5f5f5;
        }
        .admin-notif-btn:hover {
            background: #cbb90f;
        }
        .admin-notif-badge {
            position: absolute;
            top: -4px;
            right: -3px;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }
        .admin-notif-dropdown {
            position: absolute;
            top: 54px;
            right: 0;
            width: 360px;
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 18px 45px rgba(0,0,0,0.2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.2s ease;
            overflow: hidden;
        }
        .admin-notif-wrap:hover .admin-notif-dropdown,
        .admin-notif-wrap.open .admin-notif-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .admin-notif-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-bottom: 1px solid #ececec;
            background: #faf7e9;
        }
        .admin-notif-head h4 {
            margin: 0;
            font-size: 15px;
            color: #1d1d1d;
        }
        .admin-notif-head span {
            font-size: 12px;
            font-weight: 700;
            color: #cbb90f;
        }
        .admin-notif-list {
            max-height: 310px;
            overflow-y: auto;
        }
        .admin-notif-item {
            display: block;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f1f1;
            color: #222;
            text-decoration: none;
        }
        .admin-notif-item.unread {
            background: #fff9db;
        }
        .admin-notif-item:hover {
            background: #f6f6f6;
        }
        .admin-notif-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
        }
        .admin-notif-row h5 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #1d1d1d;
        }
        .admin-notif-row span {
            font-size: 11px;
            color: #777;
            white-space: nowrap;
        }
        .admin-notif-item p {
            margin: 0;
            font-size: 12px;
            color: #5a5a5a;
            line-height: 1.4;
        }
        .admin-notif-empty {
            padding: 20px 14px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        .admin-notif-view-all {
            display: block;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #18150d;
            background: #f5f5f5;
            padding: 11px 14px;
            text-decoration: none;
        }
        .admin-notif-view-all:hover {
            background: #cbb90f;
        }
        @media (max-width: 768px) {
            .admin-notif-dropdown {
                width: min(360px, calc(100vw - 20px));
                right: -12px;
            }
            .admin-profile-btn {
                margin-right: 0;
            }
            .admin-profile-menu {
                right: -6px;
                min-width: 210px;
            }
        }
    </style>
    <script>
        function toggleSubmenu(event) {
            event.preventDefault();
            const parentLi = event.currentTarget.parentElement;
            const isAlreadyOpen = parentLi.classList.contains('active');

            document.querySelectorAll('.nav-links > li.active').forEach((menuItem) => {
                if (menuItem !== parentLi) {
                    menuItem.classList.remove('active');
                }
            });

            parentLi.classList.toggle('active', !isAlreadyOpen);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.submenu a').forEach((link) => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    const parentLi = link.closest('.submenu')?.parentElement;
                    if (parentLi) {
                        parentLi.classList.add('active');
                    }
                }
            });

            const notifWrap = document.getElementById('adminNotifWrap');
            const notifBtn = notifWrap ? notifWrap.querySelector('.admin-notif-btn') : null;
            if (notifWrap && notifBtn) {
                notifBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    notifWrap.classList.toggle('open');
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('#adminNotifWrap')) {
                        notifWrap.classList.remove('open');
                    }
                });
            }

            const profileWrap = document.getElementById('adminProfileWrap');
            const profileBtn = profileWrap ? profileWrap.querySelector('.admin-profile-btn') : null;
            if (profileWrap && profileBtn) {
                profileBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const isOpen = profileWrap.classList.toggle('open');
                    profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('#adminProfileWrap')) {
                        profileWrap.classList.remove('open');
                        profileBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
</head>
<body>

<div class="header">
    <div class="user-profile">
        <div class="admin-notif-wrap" id="adminNotifWrap">
            <button type="button" class="admin-notif-btn" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($admin_notification_unread > 0): ?>
                    <span class="admin-notif-badge"><?php echo (int) min(99, $admin_notification_unread); ?><?php echo $admin_notification_unread > 99 ? '+' : ''; ?></span>
                <?php endif; ?>
            </button>
            <div class="admin-notif-dropdown">
                <div class="admin-notif-head">
                    <h4>Notifications</h4>
                    <?php if ($admin_notification_unread > 0): ?>
                        <span><?php echo (int) $admin_notification_unread; ?> new</span>
                    <?php endif; ?>
                </div>
                <div class="admin-notif-list">
                    <?php renderAdminNotificationPreview($admin_notification_items); ?>
                </div>
                <a href="notifications.php" class="admin-notif-view-all">View all notifications</a>
            </div>
        </div>
        <div class="dropdown admin-profile-wrap" id="adminProfileWrap">
            <button type="button" class="dropbtn admin-profile-btn" aria-expanded="false"><i class="fas fa-user-circle" id="admin-icon"></i>Admin</button>
            <div class="dropdown-content admin-profile-menu">
            <p class="admin-profile-head">Name: <?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></p>                
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
        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a></li>
        <li><a href="customer.php"><i class="fas fa-user"></i> Client Directory</a></li>
        <li><a href="appointments_manage.php"><i class="fas fa-calendar-alt"></i> Appointment Management</a></li>
        <li>
            <a href="products.php" class="has-submenu" onclick="toggleSubmenu(event)">
                <span class="menu-label"><i class="fas fa-box"></i> Product Operations</span>
                <i class="fas fa-chevron-down submenu-caret"></i>
            </a>
            <ul class="submenu">
                <li><a href="products.php"><i class="fas fa-cogs"></i> Product Catalog</a></li>
                <li><a href="combos.php"><i class="fas fa-layer-group"></i> Combo Management</a></li>
                <li><a href="manage_orders.php"><i class="fas fa-receipt"></i> Order Management</a></li>
            </ul>
        </li>
        <li>
            <a href="membership_manage.php" class="has-submenu" onclick="toggleSubmenu(event)">
                <span class="menu-label"><i class="fas fa-box"></i> Membership Programs</span>
                <i class="fas fa-chevron-down submenu-caret"></i>
            </a>
            <ul class="submenu">
                <li><a href="membership_manage.php"><i class="fas fa-cogs"></i> Plan Management</a></li>
                <li><a href="membership_details.php"><i class="fas fa-user-tag"></i> Membership Transactions</a></li>

            </ul>
        </li>
        <li><a href="service_manage.php"><i class="fas fa-cut"></i> Service Catalog</a></li>
        <li><a href="payment_integrations.php"><i class="fas fa-credit-card"></i> Payment Integrations</a></li>
        <li><a href="payment_manage.php"><i class="fas fa-box"></i> Payment Management</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="database_backup.php"><i class="fas fa-database"></i> Backup & Restore</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li>
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
        // Skip elements that are inside a table or are status chips
        if (alert.closest('td') || alert.classList.contains('payment-status-chip') || alert.classList.contains('status-badge')) {
            return;
        }
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
    // --- 3-DOT DROPDOWN TOGGLE ---
    function setActionDropdownState(dropdown, isOpen) {
        if (!dropdown) return;
        const menu = dropdown.querySelector('.action-dropdown-content');
        const trigger = dropdown.querySelector('.action-dots');

        dropdown.classList.toggle('show', isOpen);
        if (menu) {
            menu.style.display = isOpen ? 'block' : 'none';
            menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }
        if (trigger) {
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    }

    function closeAllActionDropdowns(exceptDropdown = null) {
        document.querySelectorAll('.action-dropdown').forEach(d => {
            if (d !== exceptDropdown) {
                setActionDropdownState(d, false);
            }
        });
    }

    function toggleActionDropdown(event, id) {
        event.preventDefault();
        event.stopPropagation();

        const dropdown = event.currentTarget.closest('.action-dropdown');
        if (!dropdown) return;

        const willOpen = !dropdown.classList.contains('show');
        closeAllActionDropdowns(dropdown);
        setActionDropdownState(dropdown, willOpen);
    }

    document.addEventListener('DOMContentLoaded', function() {
        closeAllActionDropdowns();
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown')) {
            closeAllActionDropdowns();
        }
    });
</script>
</body>
</html>
