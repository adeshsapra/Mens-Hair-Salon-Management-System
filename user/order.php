<?php
include('header.php');
include('connect.php');

$user_id = $_SESSION['user_id'];
$sale_query = "
    SELECT * FROM product_sales
    WHERE id = '$user_id'
    ORDER BY s_date DESC, s_time DESC";

$sale_result = mysqli_query($con, $sale_query);

if (!$sale_result) {
    die("Database query failed: " . mysqli_error($con));
}

$orderGroups = [];
if (mysqli_num_rows($sale_result) === 0) {
    $empty_cart_message = 'No Orders Here...';
} else {
    while ($sale_row = mysqli_fetch_assoc($sale_result)) {
        // Fetch history for each order to show progress
        $history_query = mysqli_query($con, "SELECT * FROM order_status_updates WHERE s_id = {$sale_row['s_id']} ORDER BY id ASC");
        $history = [];
        while ($h = mysqli_fetch_assoc($history_query)) {
            $history[$h['status']] = $h;
        }
        $sale_row['history'] = $history;
        $orderGroups[$sale_row['s_date'] . '|' . $sale_row['s_time']][] = $sale_row;
    }
}

$productLookup = [];
$product_query = mysqli_query($con, "SELECT p_id, p_name, p_size FROM products");
if ($product_query) {
    while ($product_row = mysqli_fetch_assoc($product_query)) {
        $product_key = strtolower(trim($product_row['p_name'])) . '|' . strtolower(trim($product_row['p_size']));
        if (!isset($productLookup[$product_key])) {
            $productLookup[$product_key] = (int) $product_row['p_id'];
        }
    }
}
?>

<style>
    /* Order Tracking Styles */
    .order-tracking-container {
        padding: 20px 0;
        margin-top: 20px;
        border-top: 1px solid #eee;
    }

    .tracking-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 30px;
    }

    .tracking-steps::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e0e0e0;
        z-index: 1;
    }

    .step {
        position: relative;
        z-index: 2;
        text-align: center;
        width: 20%;
    }

    .step-icon {
        width: 30px;
        height: 30px;
        background: #e0e0e0;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 12px;
        border: 4px solid white;
        transition: all 0.3s ease;
    }

    .step.active .step-icon {
        background: var(--brand);
        box-shadow: 0 0 10px rgba(203,185,15,0.4);
    }

    .step-text {
        font-size: 11px;
        font-weight: 600;
        color: #999;
        text-transform: capitalize;
    }

    .step.active .step-text {
        color: var(--brand);
    }

    .step-time {
        font-size: 9px;
        color: #bbb;
        display: block;
        margin-top: 2px;
    }

    .step.active .step-time {
        color: #666;
    }

    /* Modern Order Layout Upgrade */
    .order-card-modern {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card-top {
        display: flex;
        padding: 20px;
    }

    .order-img-container {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .order-info-container {
        flex: 1;
        padding-left: 20px;
    }

    .order-actions-bar {
        background: #fcfcfc;
        padding: 15px 20px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<main class="content">
<section class="order-container">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Product Orders</h1>
        <a href="../eshop.php" class="app_more" style="margin-top: 0;"><i class="fas fa-shopping-basket"></i> Continue Shopping</a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div style="background: #e6f4ea; color: #1e8e3e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orderGroups)): ?>
        <div style="padding: 60px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3); margin-top: 20px;">
            <i class="fas fa-box-open" style="font-size: 50px; color: var(--brand); margin-bottom: 20px;"></i>
            <h3>No Orders Yet</h3>
            <p style="color: #777; margin-bottom: 30px;">You haven't placed any orders yet. Explore our shop!</p>
            <a href="../eshop.php" class="app_more" style="display: inline-block; margin-top: 0;"><i class="fas fa-shopping-cart"></i> Start Shopping</a>
        </div>
    <?php else: ?>
    <div class="orders">
        <?php foreach ($orderGroups as $groupKey => $orders): ?>
            <?php
            $groupDate = $orders[0]['s_date'];
            $groupTime = $orders[0]['s_time'];
            ?>
            <div class="order-group" style="margin-bottom: 50px;">
                <h3 style="font-size: 16px; color: var(--bg1); border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: var(--brand); width: 8px; height: 8px; border-radius: 50%;"></span>
                    Ordered on <?php echo date('d M Y, h:i A', strtotime($groupDate . ' ' . $groupTime)); ?>
                </h3>

                <div class="order-items-grid">
                    <?php foreach ($orders as $sale_row): ?>
                        <?php
                            $lookupKey = strtolower(trim($sale_row['s_name'])) . '|' . strtolower(trim($sale_row['s_size']));
                            $productUrl = isset($productLookup[$lookupKey]) ? "../product_display.php?id=" . $productLookup[$lookupKey] : '';
                            $current_status = strtolower($sale_row['s_status']);
                            $can_cancel = in_array($current_status, ['pending', 'confirmed', 'processing'], true);
                        ?>
                        <div class="order-card-modern">
                            <div class="card-top">
                                <div class="order-img-container">
                                    <img src="../upload_product_photos/<?php echo htmlspecialchars($sale_row['s_img']); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>

                                <div class="order-info-container">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                        <div>
                                            <?php if ($productUrl): ?>
                                                <h2 style="font-size: 18px; margin: 0;">
                                                    <a href="<?php echo htmlspecialchars($productUrl); ?>" class="order-product-link"><?php echo htmlspecialchars($sale_row['s_name']); ?></a>
                                                </h2>
                                            <?php else: ?>
                                                <h2 style="font-size: 18px; color: var(--bg1); margin: 0;"><?php echo htmlspecialchars($sale_row['s_name']); ?></h2>
                                            <?php endif; ?>
                                            <?php
                                                $originalTotal = (float) $sale_row['s_price'] * (int) $sale_row['s_quantity'];
                                                $finalTotal = (float) $sale_row['s_total'];
                                            ?>
                                            <?php if ($originalTotal > $finalTotal): ?>
                                                <div style="font-size: 12px; color: #777; text-decoration: line-through;">₹<?php echo number_format($originalTotal, 2); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span style="font-weight: 700; color: var(--brand); font-size: 18px;">₹<?php echo number_format($finalTotal, 2); ?></span>
                                    </div>
                                    
                                    <div style="color: #666; font-size: 14px; margin-bottom: 15px;">
                                        <span style="margin-right: 15px;"><i class="fas fa-tag"></i> Size: <?php echo htmlspecialchars($sale_row['s_size']); ?></span>
                                        <span><i class="fas fa-boxes"></i> Qty: <?php echo (int) $sale_row['s_quantity']; ?></span>
                                    </div>

                                    <!-- Progress Tracker -->
                                    <div class="order-tracking-container">
                                        <?php 
                                        $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                                        ?>
                                        <div class="tracking-steps">
                                            <?php foreach ($steps as $index => $step): ?>
                                                <?php 
                                                $is_completed = isset($sale_row['history'][$step]);
                                                $display_time = $is_completed ? date('d/m h:i A', strtotime($sale_row['history'][$step]['update_date'] . ' ' . $sale_row['history'][$step]['update_time'])) : '';
                                                ?>
                                                <div class="step <?php echo $is_completed ? 'active' : ''; ?>">
                                                    <div class="step-icon">
                                                        <?php switch($step) {
                                                            case 'pending': echo '<i class="fas fa-clock"></i>'; break;
                                                            case 'confirmed': echo '<i class="fas fa-check"></i>'; break;
                                                            case 'processing': echo '<i class="fas fa-cog"></i>'; break;
                                                            case 'shipped': echo '<i class="fas fa-truck"></i>'; break;
                                                            case 'delivered': echo '<i class="fas fa-home"></i>'; break;
                                                        } ?>
                                                    </div>
                                                    <div class="step-text"><?php echo $step; ?></div>
                                                    <div class="step-time"><?php echo $display_time; ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="order-actions-bar">
                                <div>
                                    <?php
                                        if ($current_status === 'delivered') {
                                            $statusBg = '#e6f4ea';
                                            $statusColor = '#1e8e3e';
                                            $statusIcon = 'fa-check-circle';
                                        } elseif ($current_status === 'cancelled') {
                                            $statusBg = '#fce8e6';
                                            $statusColor = '#d93025';
                                            $statusIcon = 'fa-times-circle';
                                        } else {
                                            $statusBg = '#fef9c3';
                                            $statusColor = '#854d0e';
                                            $statusIcon = 'fa-info-circle';
                                        }
                                    ?>
                                    <span style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>; font-size: 12px; padding: 6px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                                        <i class="fas <?php echo $statusIcon; ?>"></i> Status: <?php echo ucfirst($sale_row['s_status']); ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <a href="invoice.php?time=<?php echo urlencode($sale_row['s_time']); ?>" class="order-action-btn order-action-primary" style="padding: 8px 15px; font-size: 13px;">
                                        <i class="fas fa-file-invoice"></i> Download Invoice
                                    </a>
                                    <?php if ($can_cancel) { ?>
                                        <form action="cancel_order.php" method="get" style="margin: 0;">
                                            <input type="hidden" name="id" value="<?php echo (int) $sale_row['s_id']; ?>">
                                            <button type="submit" class="order-action-btn order-action-danger" style="padding: 8px 15px; font-size: 13px;" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                <i class="fas fa-ban"></i> Cancel Order
                                            </button>
                                        </form>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
</main>
