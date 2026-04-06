<?php
include 'connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cartTableExists(mysqli $con, string $table): bool
{
    $table_safe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function canFulfillComboQuantity(mysqli $con, int $combo_id, int $bundle_quantity): bool
{
    if ($bundle_quantity <= 0) {
        return false;
    }

    $query = mysqli_query(
        $con,
        "SELECT cp.quantity, p.p_quantity
         FROM combo_products cp
         INNER JOIN products p ON p.p_id = cp.product_id
         WHERE cp.combo_id = {$combo_id}"
    );
    if (!$query || mysqli_num_rows($query) === 0) {
        return false;
    }

    while ($row = mysqli_fetch_assoc($query)) {
        $required = (int) $row['quantity'] * $bundle_quantity;
        if ((int) $row['p_quantity'] < $required) {
            return false;
        }
    }

    return true;
}

function comboProductFinalPrice(float $price, float $discount): float
{
    $discount = max(0, min(100, $discount));
    return round($price - (($price * $discount) / 100), 2);
}

$combo_cart_ready = cartTableExists($con, 'combo_cart') && cartTableExists($con, 'combo_products');
$errors = [];
$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($user_id <= 0) {
    header('Location: ../login.php');
    exit();
}

if (isset($_POST['update_update_btn'])) {
    $update_value = isset($_POST['update_quantity']) ? max(1, (int) $_POST['update_quantity']) : 1;
    $update_id = isset($_POST['update_quantity_id']) ? (int) $_POST['update_quantity_id'] : 0;
    $cart_type = isset($_POST['cart_type']) ? trim((string) $_POST['cart_type']) : 'product';

    if ($update_id > 0) {
        if ($cart_type === 'combo' && $combo_cart_ready) {
            $combo_query = mysqli_query($con, "SELECT combo_id, cc_price FROM combo_cart WHERE cc_id = {$update_id} AND id = {$user_id} LIMIT 1");
            if ($combo_query && ($combo_row = mysqli_fetch_assoc($combo_query))) {
                $combo_id = (int) $combo_row['combo_id'];
                if (!canFulfillComboQuantity($con, $combo_id, $update_value)) {
                    $errors['combo_' . $update_id] = 'Not Available!';
                } else {
                    $new_total = ((float) $combo_row['cc_price']) * $update_value;
                    $update_total_query = mysqli_query(
                        $con,
                        "UPDATE combo_cart SET cc_quantity = {$update_value}, cc_total = {$new_total} WHERE cc_id = {$update_id} AND id = {$user_id}"
                    );
                    if ($update_total_query) {
                        header('location:products_user.php?toast=success&msg=Cart+updated+successfully!');
                        exit();
                    }
                    $errors['combo_' . $update_id] = 'Failed to update combo quantity.';
                }
            } else {
                $errors['combo_' . $update_id] = 'Combo cart item not found.';
            }
        } else {
            $product_query = mysqli_query($con, "SELECT p_id FROM product_cart WHERE c_id = {$update_id} AND id = {$user_id} LIMIT 1");
            $product_data = $product_query ? mysqli_fetch_assoc($product_query) : null;
            $product_id = $product_data ? (int) $product_data['p_id'] : 0;

            if ($product_id > 0) {
                $stock_query = mysqli_query($con, "SELECT p_quantity FROM products WHERE p_id = {$product_id} LIMIT 1");
                $stock_data = $stock_query ? mysqli_fetch_assoc($stock_query) : null;
                $available_stock = $stock_data ? (int) $stock_data['p_quantity'] : 0;

                if ($update_value > $available_stock) {
                    $errors['product_' . $update_id] = 'Not Available!';
                } else {
                    $update_total_query = mysqli_query(
                        $con,
                        "UPDATE product_cart SET c_quantity = {$update_value}, c_total = c_price * {$update_value} WHERE c_id = {$update_id} AND id = {$user_id}"
                    );
                    if ($update_total_query) {
                        header('location:products_user.php?toast=success&msg=Cart+updated+successfully!');
                        exit();
                    }
                    $errors['product_' . $update_id] = 'Failed to update product quantity.';
                }
            } else {
                $errors['product_' . $update_id] = 'Product cart item not found.';
            }
        }
    }
}

if (isset($_GET['id'])) {
    $remove_id = (int) $_GET['id'];
    $remove_type = isset($_GET['type']) ? trim((string) $_GET['type']) : 'product';

    if ($remove_id > 0) {
        if ($remove_type === 'combo' && $combo_cart_ready) {
            mysqli_query($con, "DELETE FROM combo_cart WHERE cc_id = {$remove_id} AND id = {$user_id}");
        } else {
            mysqli_query($con, "DELETE FROM product_cart WHERE c_id = {$remove_id} AND id = {$user_id}");
        }
        header('location:products_user.php?toast=success&msg=Item+removed+from+cart!');
        exit();
    }
}

if (isset($_GET['delete_all'])) {
    mysqli_query($con, "DELETE FROM product_cart WHERE id = {$user_id}");
    if ($combo_cart_ready) {
        mysqli_query($con, "DELETE FROM combo_cart WHERE id = {$user_id}");
    }
    header('location:products_user.php?toast=success&msg=Cart+emptied+successfully!');
    exit();
}

include 'header.php';

$cart_items = [];
$grand_total = 0.0;

$select_products = mysqli_query(
    $con,
    "SELECT product_cart.*, products.*
     FROM product_cart
     JOIN products ON product_cart.p_id = products.p_id
     WHERE product_cart.id = {$user_id}"
);

if ($select_products) {
    while ($row = mysqli_fetch_assoc($select_products)) {
        $price = (float) $row['c_price'];
        $quantity = (int) $row['c_quantity'];
        $sub_total = $price * $quantity;
        $original_price = (float) $row['p_price'];
        $discount_percent = isset($row['p_discount']) ? max(0, min(100, (float) $row['p_discount'])) : 0;

        $cart_items[] = [
            'type' => 'product',
            'item_id' => (int) $row['c_id'],
            'image' => $row['p_img'],
            'name' => $row['p_name'],
            'size' => $row['p_size'],
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $sub_total,
            'original_price' => $original_price,
            'discount_percent' => $discount_percent,
            'meta' => ''
        ];
        $grand_total += $sub_total;
    }
}

if ($combo_cart_ready) {
    $combo_cart_rows = [];
    $combo_ids = [];

    $combo_cart_query = mysqli_query(
        $con,
        "SELECT cc.*, c.status
         FROM combo_cart cc
         LEFT JOIN combos c ON c.id = cc.combo_id
         WHERE cc.id = {$user_id}"
    );
    if ($combo_cart_query) {
        while ($combo_row = mysqli_fetch_assoc($combo_cart_query)) {
            $combo_cart_rows[] = $combo_row;
            $combo_ids[] = (int) $combo_row['combo_id'];
        }
    }

    $combo_meta_map = [];
    if (!empty($combo_ids)) {
        $combo_id_sql = implode(',', array_unique($combo_ids));
        $combo_products_query = mysqli_query(
            $con,
            "SELECT cp.combo_id, cp.quantity, p.p_name, p.p_price, p.p_discount
             FROM combo_products cp
             INNER JOIN products p ON p.p_id = cp.product_id
             WHERE cp.combo_id IN ({$combo_id_sql})
             ORDER BY cp.combo_id ASC, p.p_name ASC"
        );

        if ($combo_products_query) {
            while ($meta_row = mysqli_fetch_assoc($combo_products_query)) {
                $combo_id = (int) $meta_row['combo_id'];
                if (!isset($combo_meta_map[$combo_id])) {
                    $combo_meta_map[$combo_id] = [
                        'parts' => [],
                        'original_total' => 0.0
                    ];
                }

                $discount = isset($meta_row['p_discount']) ? (float) $meta_row['p_discount'] : 0;
                $final_price = comboProductFinalPrice((float) $meta_row['p_price'], $discount);
                $qty = (int) $meta_row['quantity'];

                $combo_meta_map[$combo_id]['parts'][] = $meta_row['p_name'] . ' x' . $qty;
                $combo_meta_map[$combo_id]['original_total'] += ($final_price * $qty);
            }
        }
    }

    foreach ($combo_cart_rows as $combo_row) {
        $combo_id = (int) $combo_row['combo_id'];
        $price = (float) $combo_row['cc_price'];
        $quantity = (int) $combo_row['cc_quantity'];
        $sub_total = (float) $combo_row['cc_total'];
        if ($sub_total <= 0) {
            $sub_total = $price * $quantity;
        }

        $original_unit = isset($combo_meta_map[$combo_id]) ? (float) $combo_meta_map[$combo_id]['original_total'] : $price;
        $discount_percent = $original_unit > 0 ? max(0, min(100, (($original_unit - $price) / $original_unit) * 100)) : 0;
        $parts = isset($combo_meta_map[$combo_id]) ? $combo_meta_map[$combo_id]['parts'] : [];

        $cart_items[] = [
            'type' => 'combo',
            'item_id' => (int) $combo_row['cc_id'],
            'image' => $combo_row['cc_img'] ?: 'default.jpeg',
            'name' => $combo_row['cc_name'],
            'size' => 'Combo Pack',
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $sub_total,
            'original_price' => $original_unit,
            'discount_percent' => $discount_percent,
            'meta' => implode(', ', $parts)
        ];
        $grand_total += $sub_total;
    }
}

$empty_cart_message = empty($cart_items) ? 'Your cart is empty.' : '';
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

        <div class="table-container desktop-cart-view" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 2rem;">
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
                    <?php foreach ($cart_items as $item): ?>
                        <?php
                            $error_key = $item['type'] . '_' . $item['item_id'];
                            $error_message = isset($errors[$error_key]) ? $errors[$error_key] : '';
                        ?>
                        <tr style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                            <td style="padding: 16px;">
                                <img src="../upload_product_photos/<?php echo htmlspecialchars($item['image']); ?>" style="height: 80px; width: 80px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1);" alt="">
                            </td>
                            <td style="padding: 16px; color: var(--bg1);">
                                <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if ($item['type'] === 'combo'): ?>
                                        <span style="font-size: 11px; margin-left: 6px; padding: 3px 8px; border-radius: 999px; background:#eef2ff; color:#3730a3;">COMBO</span>
                                    <?php endif; ?>
                                </div>
                                <div style="color: #666; font-size: 13px;"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['size']); ?></div>
                                <?php if ($item['type'] === 'combo' && !empty($item['meta'])): ?>
                                    <div style="color: #666; font-size: 12px; margin-top: 5px; line-height: 1.4;">
                                        Includes: <?php echo htmlspecialchars($item['meta']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 16px; font-weight: 600; color: #555;">
                                <?php if ((float) $item['discount_percent'] > 0): ?>
                                    <span style="text-decoration: line-through; color: #888; margin-right: 6px;">₹<?php echo number_format((float) $item['original_price'], 2); ?></span>
                                    <span style="color: var(--brand);">₹<?php echo number_format((float) $item['price'], 2); ?></span>
                                    <small style="display:block; color:#1e8e3e;"><?php echo number_format((float) $item['discount_percent'], 0); ?>% OFF</small>
                                <?php else: ?>
                                    ₹<?php echo number_format((float) $item['price'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 16px;">
                                <form action="" method="post" style="display: flex; align-items: center; gap: 8px;">
                                    <input type="hidden" name="update_quantity_id" value="<?php echo (int) $item['item_id']; ?>">
                                    <input type="hidden" name="cart_type" value="<?php echo htmlspecialchars($item['type']); ?>">
                                    <input type="number" name="update_quantity" min="1" value="<?php echo (int) $item['quantity']; ?>" style="width: 70px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; text-align: center;">
                                    <button type="submit" name="update_update_btn" style="background: var(--bg1); color: var(--bg2); border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s;"><i class="fas fa-sync-alt"></i></button>
                                    <?php if ($error_message): ?>
                                        <span style="color: #d93025; font-size: 12px; font-weight: bold;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?></span>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td style="padding: 16px; font-weight: 700; color: var(--brand);">₹<?php echo number_format((float) $item['subtotal'], 2); ?></td>
                            <td style="padding: 16px; text-align: right;">
                                <a href="products_user.php?id=<?php echo (int) $item['item_id']; ?>&type=<?php echo urlencode($item['type']); ?>" onclick="return confirm('Remove item from cart?')" style="background: #fce8e6; color: #d93025; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-block; transition: 0.2s;"><i class="fas fa-trash-alt"></i> Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #fafafa; border-top: 2px solid rgba(203,185,15,0.3);">
                        <td colspan="4" style="padding: 20px 16px; text-align: right; font-weight: 600; font-size: 18px; color: var(--bg1);">Grand Total:</td>
                        <td style="padding: 20px 16px; font-weight: 700; font-size: 20px; color: var(--brand);">₹<?php echo number_format((float) $grand_total, 2); ?></td>
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

        <div class="mobile-cart-view">
            <?php foreach ($cart_items as $item): ?>
                <?php
                    $error_key = $item['type'] . '_' . $item['item_id'];
                    $error_message = isset($errors[$error_key]) ? $errors[$error_key] : '';
                ?>
                <div class="mobile-cart-item">
                    <div class="m-cart-upper">
                        <div class="m-cart-img">
                            <img src="../upload_product_photos/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                        </div>
                        <div class="m-cart-details">
                            <div class="m-cart-name">
                                <?php echo htmlspecialchars($item['name']); ?>
                                <?php if ($item['type'] === 'combo'): ?>
                                    <span style="font-size: 10px; margin-left: 6px; padding: 2px 7px; border-radius: 999px; background:#eef2ff; color:#3730a3;">COMBO</span>
                                <?php endif; ?>
                            </div>
                            <div class="m-cart-size"><?php echo htmlspecialchars($item['size']); ?></div>
                            <?php if ($item['type'] === 'combo' && !empty($item['meta'])): ?>
                                <div style="font-size: 11px; color: #666; margin-bottom: 4px; line-height: 1.3;">Includes: <?php echo htmlspecialchars($item['meta']); ?></div>
                            <?php endif; ?>
                            <div class="m-cart-price-row">
                                <span class="m-cart-price">₹<?php echo number_format((float) $item['price'], 2); ?></span>
                                <?php if ((float) $item['discount_percent'] > 0): ?>
                                    <span class="m-cart-old-price">₹<?php echo number_format((float) $item['original_price'], 2); ?></span>
                                    <span class="m-cart-discount"><?php echo number_format((float) $item['discount_percent'], 0); ?>% OFF</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="m-cart-lower">
                        <form action="" method="post" class="m-cart-qty-form">
                            <input type="hidden" name="update_quantity_id" value="<?php echo (int) $item['item_id']; ?>">
                            <input type="hidden" name="cart_type" value="<?php echo htmlspecialchars($item['type']); ?>">
                            <input type="number" name="update_quantity" min="1" value="<?php echo (int) $item['quantity']; ?>" class="m-cart-qty-input">
                            <button type="submit" name="update_update_btn" class="m-cart-update-btn"><i class="fas fa-sync-alt"></i></button>
                        </form>
                        <a href="products_user.php?id=<?php echo (int) $item['item_id']; ?>&type=<?php echo urlencode($item['type']); ?>" onclick="return confirm('Remove item from cart?')" class="m-cart-remove-link">
                            <i class="fas fa-trash-alt"></i> Remove
                        </a>
                    </div>
                    <?php if ($error_message): ?>
                        <div style="color: #d93025; font-size: 12px; font-weight: bold; margin-top: 10px; text-align: center;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="m-cart-total-footer">
                <div class="m-cart-footer-row">
                    <span class="m-cart-footer-label">Subtotal</span>
                    <span class="m-cart-footer-value">₹<?php echo number_format((float) $grand_total, 2); ?></span>
                </div>
                <div class="m-cart-footer-row">
                    <span class="m-cart-footer-label">Delivery</span>
                    <span class="m-cart-footer-value" style="color: #388e3c;">FREE</span>
                </div>
                <div class="m-cart-footer-row grand-total">
                    <span class="m-cart-footer-label" style="font-weight: 700; color: #212121;">Total Amount</span>
                    <span class="m-cart-footer-value is-total">₹<?php echo number_format((float) $grand_total, 2); ?></span>
                </div>

                <form action="" method="get" style="margin-top: 15px;">
                    <input type="hidden" name="delete_all" value="1">
                    <button type="submit" class="m-empty-cart-btn">
                        <i class="fas fa-times-circle"></i> Empty Shopping Cart
                    </button>
                </form>
            </div>
        </div>

        <div class="checkout-btn" style="text-align: right;">
            <a href="checkout.php" class="proceed-btn" style="display: inline-block; padding: 14px 32px; font-size: 18px; border-radius: 30px;"><i class="fas fa-lock"></i> Secure Checkout</a>
        </div>
    <?php endif; ?>

</section>
</div>
</main>
