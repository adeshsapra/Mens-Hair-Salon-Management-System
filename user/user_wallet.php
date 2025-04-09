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
    <div class="wallet-summary">
        <h2>User Wallet</h2>
    </div>
    
    <div class="wallet-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php 
            // Initialize counter
            $transaction_count = 1;
            while ($row = mysqli_fetch_assoc($result)): 
                $total_wallet_amount += $row['amount']; // Calculate total amount
            ?>
                <div class="wallet-card">
                    <h3>Transaction : <?= $transaction_count++ ?></h3> <!-- Displaying the counter -->
                    <?php if (!empty($row['s_name'])): ?>
                        <p><strong>Product Name:</strong> <?= htmlspecialchars($row['s_name']) ?></p>
                    <?php endif; ?>
                    <p><strong>Amount:</strong> ₹<?= number_format($row['amount'], 2) ?></p>
                    <p><strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($row['date'])) ?></p>
                    <?php if (!empty($row['s_img'])): ?>
                        <img src="../upload_product_photos/<?= htmlspecialchars($row['s_img']) ?>" alt="<?= htmlspecialchars($row['s_name'] ?? 'Product Image') ?>" class="product-image">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>

    <!-- Total Amount Row -->
    <div class="total-amount">
        <h2>Total Wallet Amount: ₹<?= number_format($total_wallet_amount, 2) ?></h2>
    </div>
    <div id="app_more">
        <a href="#" class="app_more">Withdrawal Amount: ₹<?= number_format($total_wallet_amount, 2) ?></a>
    </div>
</main>

<style>
:root {
    --bg1: #18150d;
    --bg2: #eae3c2;
    --body: #a39623; 
    --brand: #cbb90f;
    --white: #fff; 
    --green: #28a745;
    --red: #dc3545; 
    --shadow: rgba(0, 0, 0, 0.2);
}

.content {
    padding: 30px;
    background-color: var(--body);
}

.wallet-summary {
    border-bottom: 2px solid var(--bg1);
    margin: 1.5rem;
    margin-bottom: 20px;
}

.wallet-summary h2 {
    color: var(--bg1);
    font-weight: 700;
}

.wallet-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 0 auto;
    margin: 3rem;
}

.wallet-card {
    padding: 20px;
    border-radius: 10px;
    background-color: var(--bg2);
    box-shadow: 0 4px 10px var(--shadow);
    transition: transform 0.3s, box-shadow 0.3s;
}

.wallet-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px var(--shadow);
}

.wallet-card h3 {
    font-size: 1.5em;
    color: var(--body);
}

.wallet-card p {
    margin: 5px 0;
    color: var(--bg1);
    font-size: 1em;
}

.product-image {
    width: 80px;
    height: auto;
    border-radius: 5px;
    margin-top: 10px;
}

.total-amount {
    background-color: var(--bg1);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-top: 30px;
    box-shadow: 0 4px 20px var(--shadow);
}

.total-amount h2 {
    color: var(--bg2);
    font-size: 25px;
}
</style>
