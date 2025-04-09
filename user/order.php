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
    <h1>Your Orders</h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orderGroups)): ?>
        <div class="empty-cart-message">
            <?php echo $empty_cart_message; ?>
            <br><a href="../eshop.php" class="empty_cart_button">Shop Now</a>
        </div>
    <?php else: ?>
    <div class="orders">
        <?php foreach ($orderGroups as $time => $orders): ?>
            <div class="order-group">
                <h3>Date : <?php echo date('Y-m-d', strtotime($time)); ?></h3>
                <?php foreach ($orders as $sale_row): ?>
                <div class="order" style="position: relative;"> 
                    <div class="order-img">
                        <img src="../upload_product_photos/<?php echo $sale_row['s_img']; ?>" alt="Premium Products">
                    </div>
                    <div class="order-details">
                        <h2><?php echo $sale_row['s_name']; ?></h2>
                        <p>Price: ₹ <?php echo $sale_row['s_price']; ?></p>
                        <p>Size: <?php echo $sale_row['s_size']; ?></p>
                        <p>Quantity: <?php echo $sale_row['s_quantity']; ?></p>
                        <p>Total Amount: ₹ <?php echo $sale_row['s_total']; ?></p>
                        <p>Date: <?php echo $sale_row['s_date']; ?></p>
                        <?php if ($sale_row['s_status'] == 'Cancelled') { ?>
                            <p style="color:red;font-size:20px;">Order Cancelled</p>
                            <div style="position: absolute; top: 10px; right: 10px;"> 
                                <form action="remove_order.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $sale_row['s_id']; ?>">
                                    <button type="submit" style="background:none; border:none; cursor:pointer; color:#18150d; font-size:22px; font-weight:bold;">&#x2715;</button>
                                </form>
                            </div>
                        <?php } else { ?>
                            <p><i style="color:green;font-size:20px;"><?php echo $sale_row['s_status']; ?></i></p>
                            <a href="invoice.php?time=<?php echo $sale_row['s_time']; ?>"><button type="button">Invoice</button></a>
                            <a href="cancel_order.php?id=<?php echo $sale_row['s_id']; ?>" onclick="return confirm('Are you sure you want to cancel this order?');">
                                <button type="button">Cancel Order</button>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div> <!-- Close order-group -->
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
</main>