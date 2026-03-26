<?php
include('connect.php');
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "
    SELECT wt.id, wt.amount, wt.date, ps.s_name, ps.s_price, ps.s_img 
    FROM wallet_transactions wt 
    LEFT JOIN product_sales ps ON wt.product_id = ps.s_id 
    WHERE wt.user_id = '$user_id'";

$result = mysqli_query($con, $query);

// Initialize total amount variable
$total_wallet_amount = 0;
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Wallet</h1>
        <div style="background: var(--bg1); color: var(--brand); padding: 12px 24px; border-radius: 12px; font-size: 20px; font-weight: 700; box-shadow: var(--shadow-sm);">
            <i class="fas fa-wallet" style="margin-right: 8px;"></i> ₹<?= number_format($total_wallet_amount, 2) ?>
        </div>
    </div>
    
    <div class="wallet-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 3rem;">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php 
            $transaction_count = 1;
            while ($row = mysqli_fetch_assoc($result)): 
            ?>
                <div class="wallet-card" style="background-color: var(--white); padding: 24px; border-radius: 14px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0, 0, 0, 0.05); transition: var(--transition); border-left: 4px solid var(--brand); display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <span style="font-weight: 600; color: #777; font-size: 13px;">#<?= $transaction_count++ ?></span>
                        <span style="color: #1e8e3e; background: #e6f4ea; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-arrow-down"></i> Received</span>
                    </div>
                    
                    <h3 style="font-size: 24px; font-weight: 700; color: var(--bg1); margin-bottom: 16px;">₹<?= number_format($row['amount'], 2) ?></h3>
                    
                    <?php if (!empty($row['s_name'])): ?>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0;">
                            <?php if (!empty($row['s_img'])): ?>
                                <img src="../upload_product_photos/<?= htmlspecialchars($row['s_img']) ?>" alt="Product" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <p style="font-size: 12px; color: #888; margin-bottom: 2px;">PRODUCT</p>
                                <p style="font-size: 14px; font-weight: 600; color: var(--bg1);"><?= htmlspecialchars($row['s_name']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <p style="margin-top: auto; color: #666; font-size: 13px;"><i class="far fa-clock"></i> <?= date('d M Y, h:i A', strtotime($row['date'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3);">
                <i class="fas fa-money-bill-wave" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i>
                <h3>No Transactions Yet</h3>
                <p style="color: #777;">Your wallet history is currently empty.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Total Amount Row -->
    <div style="background: white; border-radius: 16px; padding: 32px; text-align: center; box-shadow: var(--shadow-md); border: 2px solid rgba(203,185,15,0.2);">
        <h2 style="font-size: 18px; color: #666; margin-bottom: 16px; font-weight: 500;">Available Balance</h2>
        <h1 style="font-size: 48px; color: var(--bg1); font-weight: 700; margin-bottom: 24px;">₹<?= number_format($total_wallet_amount, 2) ?></h1>
        <a href="#" class="app_more" style="display: inline-block; padding: 16px 32px; font-size: 16px; border-radius: 30px;"><i class="fas fa-university"></i> Request Withdrawal</a>
    </div>
</main>
