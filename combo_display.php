<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combo Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include('header.php'); ?>

<?php
include('connect.php');

$user_id = $_SESSION['user_id'] ?? null;

function comboPageTableExists(mysqli $con, string $table): bool
{
    $table_safe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function comboProductFinalPrice(float $price, float $discount): float
{
    $discount = max(0, min(100, $discount));
    return round($price - (($price * $discount) / 100), 2);
}

function comboHasStock(array $combo_products, int $bundle_qty = 1): bool
{
    foreach ($combo_products as $item) {
        $required = (int) $item['quantity'] * $bundle_qty;
        if ((int) $item['available_stock'] < $required) {
            return false;
        }
    }
    return true;
}

$combo_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$combo_table_ready = comboPageTableExists($con, 'combos');
$combo_products_table_ready = comboPageTableExists($con, 'combo_products');
$combo_cart_table_ready = comboPageTableExists($con, 'combo_cart');

$combo = null;
$combo_products = [];
$combo_products_total = 0;
$combo_savings = 0;
$message = '';
$confirm = [];

if ($combo_table_ready && $combo_products_table_ready && $combo_id > 0) {
    $combo_query = mysqli_query($con, "SELECT * FROM combos WHERE id = {$combo_id} LIMIT 1");
    if ($combo_query) {
        $combo = mysqli_fetch_assoc($combo_query);
    }

    if ($combo) {
        $combo_products_query = mysqli_query(
            $con,
            "SELECT cp.product_id, cp.quantity, p.p_name, p.p_price, p.p_discount, p.p_size, p.p_img, p.p_quantity
             FROM combo_products cp
             INNER JOIN products p ON cp.product_id = p.p_id
             WHERE cp.combo_id = {$combo_id}
             ORDER BY p.p_name ASC"
        );

        if ($combo_products_query) {
            while ($row = mysqli_fetch_assoc($combo_products_query)) {
                $final_price = comboProductFinalPrice((float) $row['p_price'], (float) ($row['p_discount'] ?? 0));
                $line_total = $final_price * (int) $row['quantity'];

                $combo_products[] = [
                    'product_id' => (int) $row['product_id'],
                    'name' => $row['p_name'],
                    'size' => $row['p_size'],
                    'image' => $row['p_img'],
                    'quantity' => (int) $row['quantity'],
                    'available_stock' => (int) $row['p_quantity'],
                    'unit_final_price' => $final_price,
                    'line_total' => $line_total
                ];
                $combo_products_total += $line_total;
            }
        }
        $combo_savings = max(0, $combo_products_total - (float) $combo['price']);
    }
}

if (isset($_POST['add_to_cart'])) {
    if (!$user_id) {
        header('Location: login.php');
        exit();
    }

    if (!$combo || (int) $combo['status'] !== 1) {
        $message = 'This combo is not available right now.';
    } elseif (!$combo_cart_table_ready) {
        $message = 'Combo cart is not ready. Please run setup_combo_management.php once from admin.';
    } elseif (!comboHasStock($combo_products, 1)) {
        $message = 'One or more products in this combo are out of stock.';
    } else {
        $combo_price = (float) $combo['price'];
        $combo_name = mysqli_real_escape_string($con, $combo['name']);
        $combo_image = mysqli_real_escape_string($con, (string) $combo['image']);

        $existing_query = mysqli_query(
            $con,
            "SELECT cc_id, cc_quantity FROM combo_cart WHERE id = {$user_id} AND combo_id = {$combo_id} LIMIT 1"
        );
        if ($existing_query && mysqli_num_rows($existing_query) > 0) {
            $existing_row = mysqli_fetch_assoc($existing_query);
            $new_qty = (int) $existing_row['cc_quantity'] + 1;
            if (!comboHasStock($combo_products, $new_qty)) {
                $message = 'Not enough stock to increase this combo quantity in cart.';
            } else {
                $new_total = $combo_price * $new_qty;
                $update_query = mysqli_query(
                    $con,
                    "UPDATE combo_cart SET cc_quantity = {$new_qty}, cc_total = {$new_total} WHERE cc_id = " . (int) $existing_row['cc_id']
                );
                if ($update_query) {
                    $confirm[] = 'Combo quantity updated in cart.';
                } else {
                    $message = 'Failed to update combo cart quantity.';
                }
            }
        } else {
            $insert_query = mysqli_query(
                $con,
                "INSERT INTO combo_cart (id, combo_id, cc_name, cc_img, cc_price, cc_quantity, cc_total)
                 VALUES ({$user_id}, {$combo_id}, '{$combo_name}', '{$combo_image}', {$combo_price}, 1, {$combo_price})"
            );
            if ($insert_query) {
                $confirm[] = 'Combo added to cart successfully!';
            } else {
                $message = 'Failed to add combo to cart.';
            }
        }
    }
}

if (isset($_POST['buy_now'])) {
    if (!$user_id) {
        $_SESSION['message'] = 'Sorry..! You are not logged in.';
        header('Location: combo_display.php?id=' . $combo_id);
        exit();
    } elseif (!$combo || (int) $combo['status'] !== 1) {
        $_SESSION['message'] = 'Sorry..! This combo is not available.';
        header('Location: combo_display.php?id=' . $combo_id);
        exit();
    } elseif (!comboHasStock($combo_products, 1)) {
        $_SESSION['message'] = 'Sorry..! This combo is currently out of stock.';
        header('Location: combo_display.php?id=' . $combo_id);
        exit();
    } else {
        header('Location: user/checkout.php?combo_id=' . $combo_id);
        exit();
    }
}

$item_count = 0;
if ($user_id) {
    $product_count_query = mysqli_query($con, "SELECT COUNT(*) AS item_count FROM product_cart WHERE id = {$user_id}");
    if ($product_count_query && ($product_count_row = mysqli_fetch_assoc($product_count_query))) {
        $item_count += (int) $product_count_row['item_count'];
    }
    if ($combo_cart_table_ready) {
        $combo_count_query = mysqli_query($con, "SELECT COUNT(*) AS item_count FROM combo_cart WHERE id = {$user_id}");
        if ($combo_count_query && ($combo_count_row = mysqli_fetch_assoc($combo_count_query))) {
            $item_count += (int) $combo_count_row['item_count'];
        }
    }
}

$hero_title = $combo && !empty($combo['name']) ? $combo['name'] : 'Premium Combo Details';
?>

<div class="defualt-section">
    <img src="photos/about-img1.jpeg" alt="" class="img">
    <div class="img-content">
        <h2><?php echo htmlspecialchars($hero_title); ?></h2>
        <div class="menu">
            <a href="index.php">HOME</a> / <a href="eshop.php">Our E-shop Products</a> / <span><?php echo htmlspecialchars($hero_title); ?></span>
        </div>
    </div>
</div>

<div class="main-product-show combo-display-page">
    <div class="product-display-main">
        <div class="product-display-container">
            <?php if ($combo): ?>
                <div class="product-display-media">
                    <img
                        src="upload_product_photos/<?php echo htmlspecialchars(!empty($combo['image']) ? $combo['image'] : 'default.jpeg'); ?>"
                        alt="Combo Image"
                        class="product-image"
                    >
                </div>

                <a href="<?php echo $user_id ? 'user/products_user.php' : '#'; ?>"
                   class="cart-icon product-display-cart"
                   <?php if (!$user_id): ?>data-login-required="true"<?php endif; ?>>
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo (int) $item_count; ?></span>
                </a>

                <div class="product-details combo-details">
                    <p class="product-name"><?php echo htmlspecialchars($combo['name']); ?></p>
                    <p class="product-category"><strong>Bundle:</strong> Premium Combo Pack</p>
                    <p class="product-price">
                        <?php if ($combo_savings > 0): ?>
                            <span class="product-price-original">₹ <?php echo number_format((float) $combo_products_total, 2); ?></span>
                        <?php endif; ?>
                        <span class="product-price-final">₹ <?php echo number_format((float) $combo['price'], 2); ?></span>
                        <?php if ($combo_savings > 0): ?>
                            <span class="product-discount-badge">Save ₹ <?php echo number_format((float) $combo_savings, 2); ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="product-description"><?php echo htmlspecialchars($combo['description'] ?: 'No description available for this combo yet.'); ?></p>

                    <div class="product-features combo-feature-panel">
                        <h3>Included Products:</h3>
                        <?php if (!empty($combo_products)): ?>
                            <div class="combo-included-list">
                                <?php foreach ($combo_products as $item): ?>
                                    <a class="combo-included-item" href="product_display.php?id=<?php echo (int) $item['product_id']; ?>">
                                        <img src="upload_product_photos/<?php echo htmlspecialchars(!empty($item['image']) ? $item['image'] : 'default.jpeg'); ?>" alt="Product">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <p>Qty: <?php echo (int) $item['quantity']; ?> | Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                            <span>₹ <?php echo number_format((float) $item['line_total'], 2); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>This combo does not have linked products yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="product_display_button">
                        <a href="eshop.php" class="continue-shopping product-cta">Continue E-shop</a>
                        <form action="" method="post" class="product-display-form">
                            <button type="submit" name="add_to_cart" class="view-cart product-cta">Add to Cart</button>
                        </form>
                        <form action="" method="post" class="product-display-form">
                            <button type="submit" name="buy_now" class="view-cart product-cta">Buy Now</button>
                        </form>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="message"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php foreach ($confirm as $conf): ?>
                        <div class="confirm"><?php echo htmlspecialchars($conf); ?></div>
                    <?php endforeach; ?>
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="message"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="product-details">
                    <p class="product-name">Combo Not Found</p>
                    <p class="product-description">This combo is not available right now.</p>
                    <div class="product_display_button">
                        <a href="eshop.php" class="continue-shopping product-cta">Continue E-shop</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(event) {
    const blockedCartLink = event.target.closest('.product-display-cart[data-login-required="true"]');
    if (!blockedCartLink) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    if (typeof showToast === 'function') {
        showToast('Sorry..! You are not logged in.', 'error');
    }
});
</script>

<?php include('footer.php'); ?>
</body>
</html>
