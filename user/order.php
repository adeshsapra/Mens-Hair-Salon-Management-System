<?php
include('header.php'); 
include('connect.php');

$user_id = $_SESSION['user_id'];
$sale_query = "
    SELECT * FROM product_sales 
    WHERE id = '$user_id' 
    ORDER BY s_time DESC"; 

$sale_result = mysqli_query($con, $sale_query);

if (!$sale_result) {
    // Query failed, display error
    die("Database query failed: " . mysqli_error($con));
}

$orderGroups = [];
if (mysqli_num_rows($sale_result) === 0) {
    $empty_cart_message = 'No Orders Here...';
} else {
    while ($sale_row = mysqli_fetch_assoc($sale_result)) {
        $orderGroups[$sale_row['s_time']][] = $sale_row;  // Group by s_time
    }
}
?>

<main class="content">
<section class="order-container">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Your Orders</h1>
        <a href="../eshop.php" class="app_more" style="margin-top: 0;"><i class="fas fa-shopping-basket"></i> Buy More</a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="success-message" style="background: #e6f4ea; color: #1e8e3e; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orderGroups)): ?>
        <div style="padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3); margin-top: 20px;">
            <i class="fas fa-box-open" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i>
            <h3>No Orders Found</h3>
            <p style="color: #777; margin-bottom: 20px;"><?php echo $empty_cart_message; ?></p>
            <a href="../eshop.php" class="app_more" style="display: inline-block; margin-top: 0;"><i class="fas fa-shopping-cart"></i> Shop Now</a>
        </div>
    <?php else: ?>
    <div class="orders">
        <?php foreach ($orderGroups as $time => $orders): ?>
            <div class="order-group" style="margin-bottom: 40px;">
                <h3 style="font-size: 18px; color: var(--bg1); border-bottom: 2px solid rgba(203,185,15,0.2); padding-bottom: 8px; margin-bottom: 20px;"><i class="far fa-calendar-alt" style="color: var(--brand); margin-right: 8px;"></i> <?php echo date('d M Y, h:i A', strtotime($time)); ?></h3>
                
                <div class="order-items-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
                    <?php foreach ($orders as $sale_row): ?>
                        <div class="order" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); display: flex; overflow: hidden; position: relative;"> 
                            
                            <div class="order-img" style="width: 140px; min-width: 140px; background: #fafafa; border-right: 1px solid #eee;">
                                <img src="../upload_product_photos/<?php echo $sale_row['s_img']; ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            
                            <div class="order-details" style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <h2 style="font-size: 18px; color: var(--bg1); margin-bottom: 8px;"><?php echo htmlspecialchars($sale_row['s_name']); ?></h2>
                                <div style="color: #666; font-size: 14px; margin-bottom: 12px;">
                                    <span style="display: inline-block; margin-right: 15px;"><i class="fas fa-tag" style="color: #999;"></i> Size: <strong><?php echo htmlspecialchars($sale_row['s_size']); ?></strong></span>
                                    <span style="display: inline-block;"><i class="fas fa-boxes" style="color: #999;"></i> Qty: <strong><?php echo htmlspecialchars($sale_row['s_quantity']); ?></strong></span>
                                </div>
                                <div style="font-size: 16px; font-weight: 700; color: var(--brand); margin-bottom: 16px;">
                                    ₹<?php echo number_format($sale_row['s_total'], 2); ?>
                                </div>
                                
                                <div class="order-actions" style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 15px; gap: 10px;">
                                    <?php if ($sale_row['s_status'] == 'Cancelled') { ?>
                                        <span style="background: #fce8e6; color: #d93025; padding: 0 14px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; height: 36px;"><i class="fas fa-times-circle"></i> Cancelled</span>
                                        <form action="remove_order.php" method="post" style="margin: 0;">
                                            <input type="hidden" name="id" value="<?php echo $sale_row['s_id']; ?>">
                                            <button type="submit" class="danger-link" title="Remove from list" style="background: none; border: none; cursor: pointer; height: 36px;"><i class="fas fa-trash-alt" style="font-size: 11px;"></i> Remove</button>
                                        </form>
                                    <?php } else { 
                                        $status_color = ($sale_row['s_status'] == 'Delivered') ? '#1e8e3e' : '#854d0e';
                                        $status_bg = ($sale_row['s_status'] == 'Delivered') ? '#e6f4ea' : '#fef9c3';
                                        $status_icon = ($sale_row['s_status'] == 'Delivered') ? 'fa-check-circle' : 'fa-truck';
                                    ?>
                                        <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 0 14px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; height: 36px;"><i class="fas <?php echo $status_icon; ?>"></i> <?php echo htmlspecialchars($sale_row['s_status']); ?></span>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <a href="invoice.php?time=<?php echo $sale_row['s_time']; ?>" style="background: var(--bg1); color: var(--bg2); padding: 0 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; height: 36px; transition: 0.2s;"><i class="fas fa-file-invoice"></i> Invoice</a>
                                            <form action="cancel_order.php" method="get" style="margin:0; display: flex;">
                                                <input type="hidden" name="id" value="<?php echo $sale_row['s_id']; ?>">
                                                <button type="submit" style="background: #fce8e6; color: #d93025; border: none; padding: 0 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; height: 36px; transition: 0.2s;" onmouseover="this.style.background='#fad2ce'" onmouseout="this.style.background='#fce8e6'">
                                                    <i class="fas fa-ban"></i> Cancel
                                                </button>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div> <!-- Close order-group -->
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
</main>