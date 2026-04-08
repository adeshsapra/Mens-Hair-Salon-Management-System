<?php
include 'header.php';
include 'sidebar.php';
include 'connect.php';
require_once '../notification_helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$adminId = (int) $_SESSION['admin_id'];
notificationMarkAllAsRead($con, 'admin', $adminId);

$perPage = 10;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$totalNotifications = notificationCountAll($con, 'admin', $adminId);
$totalPages = max(1, (int) ceil($totalNotifications / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$notificationRows = notificationGetPaginated($con, 'admin', $adminId, $perPage, $offset);
?>

<div class="main-content">
    <div class="content admin-notifications-page">
        <div class="admin-notifications-page__head">
            <div>
                <h1><i class="fas fa-bell"></i> Notifications</h1>
                <p>All booking, order, membership, and payment updates from users and admin actions.</p>
            </div>
            <a href="index.php" class="admin-notifications-page__back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if (empty($notificationRows)): ?>
            <div class="admin-notifications-empty">
                <i class="fas fa-bell-slash"></i>
                <h3>No notifications yet</h3>
                <p>New user actions and status changes will appear here automatically.</p>
            </div>
        <?php else: ?>
            <div class="admin-notifications-list">
                <?php foreach ($notificationRows as $item): ?>
                    <?php $href = notificationResolveLink($item['link_url'] ?? ''); ?>
                    <a class="admin-notification-card" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="admin-notification-card__top">
                            <h4><?php echo htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h4>
                            <span><?php echo htmlspecialchars(notificationFormatTimeAgo($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars((string) ($item['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="admin-notifications-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="admin-page-btn"><i class="fas fa-angle-left"></i></a>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>" class="admin-page-btn <?php echo $page === $currentPage ? 'active' : ''; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="admin-page-btn"><i class="fas fa-angle-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-notifications-page {
    max-width: 980px;
    margin: 0 auto;
}
.admin-notifications-page__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 22px;
}
.admin-notifications-page__head h1 {
    margin: 0 0 6px;
    padding: 0;
    border: 0;
    font-size: 30px;
    color: var(--bg1);
}
.admin-notifications-page__head p {
    margin: 0;
    color: #616161;
    font-size: 14px;
}
.admin-notifications-page__back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border-radius: 10px;
    background: #1f1a10;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}
.admin-notifications-page__back:hover {
    background: #cbb90f;
    color: #18150d;
}
.admin-notifications-list {
    display: grid;
    gap: 12px;
}
.admin-notification-card {
    display: block;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.07);
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    text-decoration: none;
}
.admin-notification-card:hover {
    border-color: rgba(203, 185, 15, 0.55);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}
.admin-notification-card__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 4px;
}
.admin-notification-card__top h4 {
    margin: 0;
    color: #18150d;
    font-size: 15px;
}
.admin-notification-card__top span {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
}
.admin-notification-card p {
    margin: 0;
    font-size: 13px;
    color: #4a4a4a;
    line-height: 1.45;
}
.admin-notifications-empty {
    background: #fff;
    border: 2px dashed rgba(203, 185, 15, 0.35);
    border-radius: 14px;
    text-align: center;
    padding: 45px 20px;
    color: #666;
}
.admin-notifications-empty i {
    font-size: 42px;
    color: #cbb90f;
    margin-bottom: 12px;
}
.admin-notifications-empty h3 {
    margin: 0 0 6px;
    color: #18150d;
}
.admin-notifications-empty p {
    margin: 0;
    font-size: 14px;
}
.admin-notifications-pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}
.admin-page-btn {
    min-width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: #fff;
    color: #333;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}
.admin-page-btn:hover {
    border-color: #cbb90f;
}
.admin-page-btn.active {
    background: #cbb90f;
    border-color: #cbb90f;
    color: #18150d;
}
@media (max-width: 768px) {
    .admin-notifications-page__head {
        flex-direction: column;
        align-items: stretch;
    }
    .admin-notifications-page__head h1 {
        font-size: 24px;
    }
    .admin-notification-card__top {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

