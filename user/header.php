<?php
ob_start();
include 'connect.php';
require_once __DIR__ . '/../notification_helpers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_notification_unread = 0;
$user_notification_items = [];
$user_id = 0;

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

if ($user_id > 0) {
    $user_notification_unread = notificationGetUnreadCount($con, 'user', $user_id);
    $user_notification_items = notificationGetRecent($con, 'user', $user_id, 4);
}

if (!function_exists('renderUserNotificationPreview')) {
    function renderUserNotificationPreview($items)
    {
        if (empty($items)) {
            echo '<div class="notif-empty">No notifications yet.</div>';
            return;
        }

        foreach ($items as $item) {
            $isUnread = (int) ($item['is_read'] ?? 0) === 0;
            $itemClass = $isUnread ? 'notif-item unread' : 'notif-item';
            $href = notificationResolveLink($item['link_url'] ?? '');
            echo '<a class="' . $itemClass . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
            echo '<div class="notif-title-row">';
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
            <div class="mobile-topbar-actions">
                <div class="notif-wrap" id="userNotifMobile">
                    <button type="button" class="notif-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($user_notification_unread > 0): ?>
                            <span class="notif-badge"><?php echo (int) min(99, $user_notification_unread); ?><?php echo $user_notification_unread > 99 ? '+' : ''; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown">
                        <div class="notif-head">
                            <h4>Notifications</h4>
                            <?php if ($user_notification_unread > 0): ?>
                                <span><?php echo (int) $user_notification_unread; ?> new</span>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php renderUserNotificationPreview($user_notification_items); ?>
                        </div>
                        <a class="notif-view-all" href="notifications.php">View all notifications</a>
                    </div>
                </div>
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Overlay for closing sidebar on mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <img src="../upload_img/<?php echo $profile_image; ?>" alt="Salon Logo">
            </div>
            <div class="sidebar-notification">
                <div class="notif-wrap" id="userNotifDesktop">
                    <button type="button" class="notif-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($user_notification_unread > 0): ?>
                            <span class="notif-badge"><?php echo (int) min(99, $user_notification_unread); ?><?php echo $user_notification_unread > 99 ? '+' : ''; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown">
                        <div class="notif-head">
                            <h4>Notifications</h4>
                            <?php if ($user_notification_unread > 0): ?>
                                <span><?php echo (int) $user_notification_unread; ?> new</span>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php renderUserNotificationPreview($user_notification_items); ?>
                        </div>
                        <a class="notif-view-all" href="notifications.php">View all notifications</a>
                    </div>
                </div>
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
                    <li><a href="notifications.php" class="<?= ($current_page == 'notifications.php') ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a></li>
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

        // Notification dropdowns (click support for mobile + desktop)
        const notifWraps = document.querySelectorAll('.notif-wrap');
        const hoverLockStorageKey = 'userNotifHoverLockUntil';
        const hoverLockUntil = parseInt(sessionStorage.getItem(hoverLockStorageKey) || '0', 10);

        if (hoverLockUntil > Date.now()) {
            document.body.classList.add('notif-hover-lock');
            setTimeout(() => {
                document.body.classList.remove('notif-hover-lock');
                sessionStorage.removeItem(hoverLockStorageKey);
            }, hoverLockUntil - Date.now());
        } else {
            sessionStorage.removeItem(hoverLockStorageKey);
        }

        notifWraps.forEach((wrap) => {
            const btn = wrap.querySelector('.notif-btn');
            if (!btn) return;
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                notifWraps.forEach((el) => {
                    if (el !== wrap) {
                        el.classList.remove('open');
                    }
                });
                wrap.classList.toggle('open');
            });
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.notif-wrap')) {
                notifWraps.forEach((wrap) => wrap.classList.remove('open'));
            }
        });

        // Prevent hover flicker immediately after page navigation from sidebar menu click
        document.querySelectorAll('.sidebar nav a[href]').forEach((link) => {
            const href = (link.getAttribute('href') || '').trim();
            if (!href || href === '#' || href.startsWith('javascript:')) {
                return;
            }
            link.addEventListener('click', () => {
                sessionStorage.setItem(hoverLockStorageKey, String(Date.now() + 1000));
            });
        });
    </script>
    
<!-- ========================================== -->
<!-- GLOBAL TOAST & MODAL SYSTEM (User Dashboard) -->
<!-- ========================================== -->
<style>
/* Notifications */
.mobile-topbar-actions {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}
.sidebar-notification {
    display: flex;
    justify-content: flex-end;
    margin: 4px 0 16px;
    padding-right: 10px;
}
.notif-wrap {
    position: relative;
    z-index: 1200;
}
.notif-btn {
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 50%;
    background: #f4eed5;
    color: #201b0f;
    cursor: pointer;
    font-size: 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(24, 21, 13, 0.18);
    position: relative;
}
.notif-btn:hover {
    background: #cbb90f;
}
.mobile-topbar .notif-btn {
    width: 2.35rem;
    height: 2.35rem;
    min-width: 2.35rem;
    min-height: 2.35rem;
    max-width: 2.35rem;
    max-height: 2.35rem;
    flex: 0 0 2.35rem;
    aspect-ratio: 1 / 1;
    padding: 0;
    border-radius: 999px;
    box-sizing: border-box;
    line-height: 1;
    appearance: none;
    -webkit-appearance: none;
    font-size: 1.05rem;
    box-shadow: none;
}
.mobile-topbar .notif-badge {
    top: -5px;
    right: -4px;
}
.notif-badge {
    position: absolute;
    top: -3px;
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
.notif-dropdown {
    position: absolute;
    top: 54px;
    right: 0;
    width: 340px;
    background: #fff;
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all 0.2s ease;
    overflow: hidden;
}
body:not(.notif-hover-lock) .notif-wrap:hover .notif-dropdown,
.notif-wrap.open .notif-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.notif-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    border-bottom: 1px solid #ececec;
    background: #faf7e9;
}
.notif-head h4 {
    margin: 0;
    font-size: 15px;
    color: #1d1d1d;
}
.notif-head span {
    font-size: 12px;
    font-weight: 700;
    color: #cbb90f;
}
.notif-list {
    max-height: 300px;
    overflow-y: auto;
}
.notif-item {
    display: block;
    padding: 10px 14px;
    border-bottom: 1px solid #f1f1f1;
    color: #222;
}
.notif-item.unread {
    background: #fff9db;
}
.notif-item:hover {
    background: #f6f6f6;
}
.notif-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 3px;
}
.notif-title-row h5 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #1d1d1d;
}
.notif-title-row span {
    font-size: 11px;
    color: #777;
    white-space: nowrap;
}
.notif-item p {
    margin: 0;
    font-size: 12px;
    color: #5a5a5a;
    line-height: 1.4;
}
.notif-empty {
    padding: 20px 14px;
    text-align: center;
    font-size: 13px;
    color: #666;
}
.notif-view-all {
    display: block;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    padding: 11px 14px;
    color: #18150d;
    background: #f5f5f5;
}
.notif-view-all:hover {
    background: #cbb90f;
}
@media (max-width: 992px) {
    .sidebar-notification {
        display: none;
    }
    .notif-dropdown {
        right: -8px;
        width: min(340px, calc(100vw - 20px));
    }
}
@media (min-width: 993px) {
    .sidebar {
        overflow: visible !important;
        z-index: 1100;
    }
    .sidebar nav {
        overflow-y: auto;
        overflow-x: visible;
        max-height: calc(100vh - 220px);
        padding-right: 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .sidebar nav::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }
    .sidebar-notification {
        position: relative;
        z-index: 1500;
    }
    .mobile-topbar .notif-wrap {
        display: none;
    }
    #userNotifDesktop {
        position: relative;
        z-index: 1501;
    }
    #userNotifDesktop .notif-dropdown {
        top: -8px;
        left: calc(100% + 14px);
        right: auto;
        z-index: 1600;
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.25);
    }
    #userNotifDesktop .notif-dropdown::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 22px;
        width: 14px;
        height: 14px;
        background: #fff;
        border-left: 1px solid rgba(0, 0, 0, 0.08);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        transform: rotate(45deg);
    }
}

/* Global Custom Confirm Modal */
#global-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    z-index: 99999; display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0; transition: all 0.3s ease;
}
#global-confirm-overlay.show { visibility: visible; opacity: 1; }
.global-confirm-box {
    background: #fff; padding: 30px; border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 350px; text-align: center;
    transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
#global-confirm-overlay.show .global-confirm-box { transform: translateY(0) scale(1); }
.global-confirm-icon { font-size: 40px; color: #f59e0b; margin-bottom: 15px; }
.global-confirm-text { font-size: 18px; color: #333; margin-bottom: 25px; font-weight: 500; }
.global-confirm-actions { display: flex; gap: 15px; justify-content: center; }
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
.global-toast.show { transform: translateX(0); }
.toast-success { background: #10b981; border-left: 5px solid #059669; }
.toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
.toast-info { background: #3b82f6; border-left: 5px solid #2563eb; }
</style>

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
function showToast(message, type = 'success') {
    const container = document.getElementById('global-toast-container');
    const toast = document.createElement('div');
    toast.className = `global-toast toast-${type}`;
    let icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    // Convert PHP static messages to toasts
    document.querySelectorAll('.message, .success, .confirm, .error').forEach(alert => {
        let text = alert.innerText.trim();
        if(text) {
            let type = (alert.classList.contains('message') || alert.classList.contains('error')) ? 'error' : 'success';
            showToast(text, type);
        }
        alert.style.display = 'none';
    });

    // Check for PHP Session toasts
    <?php if (isset($_SESSION['toast-msg'])): ?>
        showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo isset($_SESSION['toast-type']) ? $_SESSION['toast-type'] : 'success'; ?>");
        <?php 
            unset($_SESSION['toast-msg']);
            unset($_SESSION['toast-type']);
        ?>
    <?php endif; ?>

    // Check for URL Parameter toasts
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('toast')) {
        let msg = urlParams.get('msg') || 'Action completed successfully!';
        showToast(msg, urlParams.get('toast'));
        window.history.replaceState(null, '', window.location.pathname);
    }
});

let confirmCallback = null;
const confirmOverlay = document.getElementById('global-confirm-overlay');
const confirmMsgElem = document.getElementById('global-confirm-msg');
document.getElementById('gc-cancel-btn').onclick = () => { confirmOverlay.classList.remove('show'); };
document.getElementById('gc-confirm-btn').onclick = () => {
    if(confirmCallback) confirmCallback();
    confirmOverlay.classList.remove('show');
};

document.addEventListener('click', function(e) {
    let el = e.target.closest('[onclick*="confirm("]');
    if (el) {
        let match = el.getAttribute('onclick').match(/confirm\(\s*['"](.*?)['"]\s*\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            confirmMsgElem.innerText = match[1];
            confirmCallback = () => {
                el.removeAttribute('onclick');
                if (el.tagName === 'A' && el.href) window.location.href = el.href;
                else el.click();
            };
            confirmOverlay.classList.add('show');
        }
    }
}, true);

// Global intercepter for onclick="alert(...)"
document.addEventListener('click', function(e) {
    let el = e.target.closest('[onclick*="alert("]');
    if (el) {
        let match = el.getAttribute('onclick').match(/alert\(\s*['"](.*?)['"]\s*\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            showToast(match[1], 'error');
        }
    }
}, true);
</script>
