<?php
include 'connect.php';
include 'header.php';
require_once 'wallet_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
ensureUserWallet($con, $user_id);
$wallet_balance = getUserWalletBalance($con, $user_id);

$transactionsQuery = mysqli_query(
    $con,
    "
    SELECT
        wt.id,
        wt.amount,
        wt.type,
        wt.source,
        wt.order_id,
        wt.sale_id,
        wt.date,
        ps.s_name,
        ps.s_img
    FROM wallet_transactions wt
    LEFT JOIN product_sales ps ON ps.s_id = COALESCE(wt.order_id, wt.sale_id)
    WHERE wt.user_id = {$user_id}
    ORDER BY wt.id DESC
    "
);
?>

<main class="content">
    <div class="header-with-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <h1 style="margin-bottom:0;">Your Wallet</h1>
        <div style="background:var(--bg1);color:var(--brand);padding:12px 24px;border-radius:12px;font-size:20px;font-weight:700;box-shadow:var(--shadow-sm);">
            <i class="fas fa-wallet" style="margin-right:8px;"></i> ₹<?php echo number_format($wallet_balance, 2); ?>
        </div>
    </div>

    <div class="wallet-container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;margin-bottom:3rem;">
        <?php if ($transactionsQuery && mysqli_num_rows($transactionsQuery) > 0): ?>
            <?php $txnCount = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($transactionsQuery)): ?>
                <?php
                    $type = strtolower($row['type'] ?? 'credit');
                    $source = strtolower($row['source'] ?? 'refund');
                    $isCredit = $type === 'credit';
                    $tagColor = $isCredit ? '#1e8e3e' : '#b06000';
                    $tagBg = $isCredit ? '#e6f4ea' : '#fef7e0';
                    $tagIcon = $isCredit ? 'fa-arrow-down' : 'fa-arrow-up';
                    $tagText = $isCredit ? 'Credit' : 'Debit';
                    $sourceLabel = ucwords(str_replace('_', ' ', $source));
                    $orderRef = !empty($row['order_id']) ? (int) $row['order_id'] : (!empty($row['sale_id']) ? (int) $row['sale_id'] : null);
                ?>
                <div class="wallet-card" style="background:#fff;padding:24px;border-radius:14px;box-shadow:var(--shadow-sm);border:1px solid rgba(0,0,0,0.05);border-left:4px solid var(--brand);display:flex;flex-direction:column;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                        <span style="font-weight:600;color:#777;font-size:13px;">#<?php echo $txnCount++; ?></span>
                        <span style="color:<?php echo $tagColor; ?>;background:<?php echo $tagBg; ?>;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                            <i class="fas <?php echo $tagIcon; ?>"></i> <?php echo $tagText; ?>
                        </span>
                    </div>

                    <h3 style="font-size:24px;font-weight:700;color:var(--bg1);margin-bottom:12px;">
                        <?php echo $isCredit ? '+' : '-'; ?>₹<?php echo number_format((float) $row['amount'], 2); ?>
                    </h3>

                    <p style="font-size:13px;color:#666;margin-bottom:10px;">
                        <i class="fas fa-receipt"></i> Source: <strong><?php echo htmlspecialchars($sourceLabel); ?></strong>
                    </p>

                    <?php if ($orderRef): ?>
                        <p style="font-size:13px;color:#666;margin-bottom:12px;">
                            <i class="fas fa-hashtag"></i> Order ID: <strong><?php echo $orderRef; ?></strong>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($row['s_name'])): ?>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #f0f0f0;">
                            <?php if (!empty($row['s_img'])): ?>
                                <img src="../upload_product_photos/<?php echo htmlspecialchars($row['s_img']); ?>" alt="Product" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                            <?php endif; ?>
                            <div>
                                <p style="font-size:12px;color:#888;margin-bottom:2px;">PRODUCT</p>
                                <p style="font-size:14px;font-weight:600;color:var(--bg1);"><?php echo htmlspecialchars($row['s_name']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p style="margin-top:auto;color:#666;font-size:13px;">
                        <i class="far fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($row['date'])); ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column:1 / -1;padding:40px;text-align:center;background:white;border-radius:14px;border:2px dashed rgba(203,185,15,0.3);">
                <i class="fas fa-money-bill-wave" style="font-size:40px;color:var(--brand);margin-bottom:16px;"></i>
                <h3>No Transactions Yet</h3>
                <p style="color:#777;">Your wallet history is currently empty.</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="background:white;border-radius:16px;padding:32px;text-align:center;box-shadow:var(--shadow-md);border:2px solid rgba(203,185,15,0.2);">
        <h2 style="font-size:18px;color:#666;margin-bottom:16px;font-weight:500;">Available Balance</h2>
        <h1 style="font-size:48px;color:var(--bg1);font-weight:700;margin-bottom:0;">₹<?php echo number_format($wallet_balance, 2); ?></h1>
    </div>
</main>
