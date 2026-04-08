<?php
include 'header.php';
include 'connect.php';
require_once '../notification_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
notificationMarkAllAsRead($con, 'user', $userId);

$perPage = 10;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$totalNotifications = notificationCountAll($con, 'user', $userId);
$totalPages = max(1, (int) ceil($totalNotifications / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$notificationRows = notificationGetPaginated($con, 'user', $userId, $perPage, $offset);
?>

<main class="content">
    <section class="notifications-page">
        <div class="notifications-page__head">
            <div>
                <h1><i class="fas fa-bell"></i> Notifications</h1>
                <p>All your booking, order, membership, and status updates in one place.</p>
            </div>
            <a href="index.php" class="notifications-page__back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if (empty($notificationRows)): ?>
            <div class="notifications-empty">
                <i class="fas fa-bell-slash"></i>
                <h3>No notifications yet</h3>
                <p>When your order, appointment, or membership updates, you will see it here.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notificationRows as $item): ?>
                    <?php $href = notificationResolveLink($item['link_url'] ?? ''); ?>
                    <a class="notification-card" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="notification-card__top">
                            <h4><?php echo htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h4>
                            <span><?php echo htmlspecialchars(notificationFormatTimeAgo($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars((string) ($item['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="notifications-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="page-btn"><i class="fas fa-angle-left"></i></a>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>" class="page-btn <?php echo $page === $currentPage ? 'active' : ''; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="page-btn"><i class="fas fa-angle-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<style>
.notifications-page {
    max-width: 980px;
    margin: 0 auto;
}
.notifications-page__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
}
.notifications-page__head h1 {
    border: 0;
    margin: 0 0 6px;
    padding: 0;
    font-size: 30px;
    color: var(--bg1);
}
.notifications-page__head p {
    margin: 0;
    color: #666;
    font-size: 14px;
}
.notifications-page__back {
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
.notifications-page__back:hover {
    background: var(--brand);
    color: #18150d;
}
.notifications-list {
    display: grid;
    gap: 12px;
}
.notification-card {
    display: block;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
}
.notification-card:hover {
    border-color: rgba(203, 185, 15, 0.5);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}
.notification-card__top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    margin-bottom: 4px;
}
.notification-card__top h4 {
    margin: 0;
    font-size: 15px;
    color: #18150d;
}
.notification-card__top span {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
}
.notification-card p {
    margin: 0;
    font-size: 13px;
    color: #4a4a4a;
    line-height: 1.45;
}
.notifications-empty {
    background: #fff;
    border: 2px dashed rgba(203, 185, 15, 0.35);
    border-radius: 14px;
    text-align: center;
    padding: 45px 20px;
    color: #666;
}
.notifications-empty i {
    font-size: 42px;
    color: var(--brand);
    margin-bottom: 12px;
}
.notifications-empty h3 {
    margin: 0 0 6px;
    color: #18150d;
}
.notifications-empty p {
    margin: 0;
    font-size: 14px;
}
.notifications-pagination {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}
.page-btn {
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
}
.page-btn:hover {
    border-color: #cbb90f;
    color: #18150d;
}
.page-btn.active {
    background: #cbb90f;
    border-color: #cbb90f;
    color: #18150d;
}
@media (max-width: 768px) {
    .notifications-page__head {
        flex-direction: column;
        align-items: stretch;
    }
    .notifications-page__head h1 {
        font-size: 24px;
    }
    .notification-card__top {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

