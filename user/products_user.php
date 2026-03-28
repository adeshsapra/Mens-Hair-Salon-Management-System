<?php 
include 'connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if(isset($_POST['update_update_btn'])){
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];

    $product_query = mysqli_query($con, "SELECT p_id FROM product_cart WHERE c_id = '$update_id'");
    $product_data = mysqli_fetch_assoc($product_query);
    $product_id = $product_data['p_id'];

    $stock_query = mysqli_query($con, "SELECT p_quantity FROM products WHERE p_id = '$product_id'");
    $stock_data = mysqli_fetch_assoc($stock_query);
    $available_stock = $stock_data['p_quantity'];

    if ($update_value > $available_stock) {
        $errors[$update_id] = "Not Available!";
    } else { 
        // Update quantity and sub_total
        $update_total_query = mysqli_query($con, "UPDATE `product_cart` SET c_quantity = '$update_value', c_total = c_price * '$update_value' WHERE c_id = '$update_id'");
        if($update_total_query){
            // Recalculate the grand total
            $grand_total_query = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '{$_SESSION['user_id']}'");
            $grand_total_data = mysqli_fetch_assoc($grand_total_query);
            $grand_total = $grand_total_data['grand_total'];

            // Update grand total in the database
            mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = '$grand_total' WHERE id = '{$_SESSION['user_id']}'");

            header('location:products_user.php?toast=success&msg=Item+removed+from+cart!');
            exit();
        }
    }
}

if(isset($_GET['id'])){
    $remove_id = $_GET['id'];

    // Remove product from cart
    mysqli_query($con, "DELETE FROM `product_cart` WHERE c_id = '$remove_id'");

    // Recalculate the grand total after removal
    $grand_total_query = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '{$_SESSION['user_id']}'");
    $grand_total_data = mysqli_fetch_assoc($grand_total_query);
    $grand_total = $grand_total_data['grand_total'] ?? 0;

    // Update the grand total after removal
    mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = '$grand_total' WHERE id = '{$_SESSION['user_id']}'");

    header('location:products_user.php?toast=success&msg=Item+removed+from+cart!');
    exit();
}

if(isset($_GET['delete_all'])){
    if(mysqli_query($con, "DELETE FROM `product_cart` WHERE id = '{$_SESSION['user_id']}'")) {
        // Set grand total to 0 after all products are removed
        mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = 0 WHERE id = '{$_SESSION['user_id']}'");
        header('Location: products_user.php?toast=success&msg=Item+removed+from+cart!');
        exit();
    } else {
        die("Error emptying cart: " . mysqli_error($con));
    }
}

include 'header.php'; // Included here to prevent output before header()

$empty_cart_message = '';
$select_cart = mysqli_query($con, "
    SELECT product_cart.*, products.*
    FROM product_cart
    JOIN products ON product_cart.p_id = products.p_id
    WHERE product_cart.id = '{$_SESSION['user_id']}'
");
if (mysqli_num_rows($select_cart) === 0) {
    $empty_cart_message = 'Your cart is empty.';
}
?>

<main class="content">
<div class="product-container">
<section class="shopping-cart">

   <?php if ($empty_cart_message): ?>
        <div style="padding: 40px; text-align: center; background: white; border-radius: 14px; border: 2px dashed rgba(203,185,15,0.3); margin-top: 20px;">
            <i class="fas fa-shopping-cart" style="font-size: 40px; color: var(--brand); margin-bottom: 16px;"></i>
            <h3>Your Cart is Empty</h3>
            <p style="color: #777; margin-bottom: 20px;">Looks like you haven't added anything to your cart yet.</p>
            <a href="../eshop.php" class="app_more" style="display: inline-block; margin-top: 0;"><i class="fas fa-store"></i> Browse Products</a>
        </div>
    <?php else: ?>
        <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0;">Shopping Cart</h1>
            <a href="../eshop.php" class="app_more" style="margin-top: 0;"><i class="fas fa-store"></i> Keep Shopping</a>
        </div>
        
        <div class="table-container" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 2rem;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: var(--bg1); color: var(--bg2);">
                        <th style="padding: 16px; font-weight: 500;">Product</th>
                        <th style="padding: 16px; font-weight: 500;">Details</th>
                        <th style="padding: 16px; font-weight: 500;">Price</th>
                        <th style="padding: 16px; font-weight: 500;">Quantity</th>
                        <th style="padding: 16px; font-weight: 500;">Subtotal</th>
                        <th style="padding: 16px; font-weight: 500; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $grand_total = 0;
                    if (mysqli_num_rows($select_cart) > 0) {
                        while ($fetch_product = mysqli_fetch_assoc($select_cart)) {
                            $original_price = (float)$fetch_product['p_price'];
                            $discount_percent = isset($fetch_product['p_discount']) ? max(0, min(100, (float) $fetch_product['p_discount'])) : 0;
                            $price = (float)$fetch_product['c_price'];
                            $quantity = (int)$fetch_product['c_quantity'];
                            $sub_total = $price * $quantity;

                            $error_message = isset($errors[$fetch_product['c_id']]) ? $errors[$fetch_product['c_id']] : '';
                ?>
                <tr style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                    <td style="padding: 16px;"><img src="../upload_product_photos/<?php echo $fetch_product['p_img']; ?>" style="height: 80px; width: 80px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1);" alt=""></td>
                    <td style="padding: 16px; color: var(--bg1);">
                        <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;"><?php echo htmlspecialchars($fetch_product['p_name']); ?></div>
                        <div style="color: #666; font-size: 13px;"><i class="fas fa-tag"></i> Size: <?php echo htmlspecialchars($fetch_product['p_size']); ?></div>
                    </td> 
                    <td style="padding: 16px; font-weight: 600; color: #555;">
                        <?php if ($discount_percent > 0): ?>
                            <span style="text-decoration: line-through; color: #888; margin-right: 6px;">₹<?php echo number_format($original_price, 2); ?></span>
                            <span style="color: var(--brand);">₹<?php echo number_format($price, 2); ?></span>
                            <small style="display:block; color:#1e8e3e;"><?php echo number_format($discount_percent, 0); ?>% OFF</small>
                        <?php else: ?>
                            ₹<?php echo number_format($price, 2); ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 16px;">
                        <form action="" method="post" style="display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="update_quantity_id" value="<?php echo htmlspecialchars($fetch_product['c_id']); ?>">
                            <input type="number" name="update_quantity" min="1" value="<?php echo htmlspecialchars($fetch_product['c_quantity']); ?>" style="width: 70px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; text-align: center;">
                            <button type="submit" name="update_update_btn" style="background: var(--bg1); color: var(--bg2); border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s;"><i class="fas fa-sync-alt"></i></button>
                            <?php if ($error_message): ?>
                                <span style="color: #d93025; font-size: 12px; font-weight: bold;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?></span>
                            <?php endif; ?>
                        </form>   
                    </td>
                    <td style="padding: 16px; font-weight: 700; color: var(--brand);">₹<?php echo number_format($sub_total, 2); ?></td>
                    <td style="padding: 16px; text-align: right;">
                        <a href="products_user.php?id=<?php echo htmlspecialchars($fetch_product['c_id']); ?>" onclick="return confirm('Remove item from cart?')" style="background: #fce8e6; color: #d93025; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-block; transition: 0.2s;"><i class="fas fa-trash-alt"></i> Remove</a>
                    </td>
                </tr>
                <?php
                        $grand_total += $sub_total;  
                        }
                    }
                ?>
                <tr style="background: #fafafa; border-top: 2px solid rgba(203,185,15,0.3);">
                    <td colspan="4" style="padding: 20px 16px; text-align: right; font-weight: 600; font-size: 18px; color: var(--bg1);">Grand Total:</td>
                    <td style="padding: 20px 16px; font-weight: 700; font-size: 20px; color: var(--brand);">₹<?php echo number_format($grand_total, 2); ?></td>
                    <td style="padding: 20px 16px; text-align: right;">
                        <form action="" method="get" style="margin: 0;">
                            <input type="hidden" name="delete_all" value="1">
                            <button type="submit" class="danger-link" style="background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fas fa-times-circle"></i> Empty Cart
                            </button>
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="checkout-btn" style="text-align: right;">
            <a href="checkout.php" class="proceed-btn" style="display: inline-block; padding: 14px 32px; font-size: 18px; border-radius: 30px;"><i class="fas fa-lock"></i> Secure Checkout</a>
        </div>
    <?php endif; ?>

</section>
</div>
</main>

