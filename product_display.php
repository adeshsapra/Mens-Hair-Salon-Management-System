<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- header -->
    <?php 
    include('header.php'); 
    ?>
    <!-- header -->

    <?php
    include('connect.php');
    $user_id = $_SESSION['user_id'] ?? null;

    function productDisplayTableExists(mysqli $con, string $table): bool {
        $table_safe = mysqli_real_escape_string($con, $table);
        $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
        return $result && mysqli_num_rows($result) > 0;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $category_table_ready = false;
    $category_column_ready = false;

    $category_table_check = mysqli_query($con, "SHOW TABLES LIKE 'product_categories'");
    if ($category_table_check && mysqli_num_rows($category_table_check) > 0) {
        $category_table_ready = true;
    }

    $category_column_check = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'category_id'");
    if ($category_column_check && mysqli_num_rows($category_column_check) > 0) {
        $category_column_ready = true;
    }

    if ($category_table_ready && $category_column_ready) {
        $query = "SELECT p.*, pc.category_name
                  FROM products p
                  LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                  WHERE p.p_id = $id
                  LIMIT 1";
    } else {
        $query = "SELECT * FROM products WHERE p_id = $id LIMIT 1";
    }

    $all_product = $con->query($query);
    $product = mysqli_fetch_assoc($all_product);
    $product_category_name = 'Uncategorized';
    if ($product && isset($product['category_name']) && $product['category_name'] !== null && $product['category_name'] !== '') {
        $product_category_name = $product['category_name'];
    }
    $discount_percent = $product ? max(0, min(100, (float) ($product['p_discount'] ?? 0))) : 0;
    $original_price = $product ? (float) $product['p_price'] : 0;
    $discounted_price = $original_price - (($original_price * $discount_percent) / 100);
    $hero_product_title = ($product && !empty($product['p_name'])) ? $product['p_name'] : 'Product Details';

    // Check if user is logged in
    if (isset($_POST['add_to_cart'])) {
        if (!$user_id) {
            header("Location: login.php");
            exit();
        }

        if ($product) {
            $available_quantity = $product['p_quantity']; // Assuming you have a column for quantity

            // Check if the product is in stock
            if ($available_quantity > 0) {
                $c_name = $product['p_name'];
                $c_img = $product['p_img'];
                $c_price = $discounted_price;
                $c_size = $product['p_size'];
                $c_quantity = 1;
                $c_total = $c_price * $c_quantity;

                // Check if product already exists in cart
                $select_cart = mysqli_query($con, "SELECT * FROM product_cart WHERE id = '$user_id' AND p_id = '$id'");
                if (mysqli_num_rows($select_cart) > 0) {
                    $confirm[] = 'Product already exists in the cart!';
                } else {
                    // Insert product into cart
                    $product_insert = mysqli_query($con, "INSERT INTO product_cart (id, p_id, c_name, c_img, c_price, c_size, c_quantity, c_total)
                                                           VALUES ('$user_id', '$id', '$c_name', '$c_img', '$c_price', '$c_size', '$c_quantity', '$c_total')");
                    if ($product_insert) {
                        $confirm[] = "Product added to cart successfully!";
                    } else {
                        $confirm[] = "Failed to add product to cart.";
                    }
                }
            } else {
                $message = "Quantity not available.";
            }
        } else {
            $message = "Product not found.";
        }
    }

    if (isset($_POST['buy_now'])) {
        if (!$user_id) {
            $_SESSION['message'] = "Sorry..! You are not logged in.";
            header("Location: product_display.php?id=" . $id);
            exit();
        } elseif (!$product || (int) $product['p_quantity'] <= 0) {
            $_SESSION['message'] = "Sorry..! This product is out of stock.";
            header("Location: product_display.php?id=" . $id);
            exit();
        } else {
            // Proceed to checkout
            header("Location: user/checkout.php?id=" . $id);
            exit();
        }
    }

    // Count items in cart
    if ($user_id) {
        $count_query = mysqli_query($con, "SELECT COUNT(*) AS item_count FROM product_cart WHERE id='$user_id'");
        $count_data = mysqli_fetch_assoc($count_query);
        $item_count = (int) $count_data['item_count'];

        if (productDisplayTableExists($con, 'combo_cart')) {
            $combo_count_query = mysqli_query($con, "SELECT COUNT(*) AS item_count FROM combo_cart WHERE id='$user_id'");
            $combo_count_data = $combo_count_query ? mysqli_fetch_assoc($combo_count_query) : null;
            $item_count += (int) ($combo_count_data['item_count'] ?? 0);
        }
    }
    ?>

    <!-- defualt section -->
    <div class="defualt-section">
        <img src="photos/about-img1.jpeg" alt="" class="img">
        <div class="img-content">
            <h2><?php echo htmlspecialchars($hero_product_title); ?></h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <a href="eshop.php">Our E-shop Products</a> / <span><?php echo htmlspecialchars($hero_product_title); ?></span>
            </div>
        </div>
    </div>
    <!-- default section -->

    <div class="main-product-show">
        <div class="product-display-main">
            <div class="product-display-container">
                <?php if ($product): ?>
                <div class="product-display-media">
                <img src="upload_product_photos/<?php echo $product["p_img"]; ?>" alt="Product Image" class="product-image">
                </div>
                <a href="<?php echo $user_id ? 'user/products_user.php' : '#'; ?>"
                   class="cart-icon product-display-cart"
                   <?php if (!$user_id): ?>data-login-required="true"<?php endif; ?>>
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo isset($item_count) ? $item_count : 0; ?></span>
                </a>
                <div class="product-details">
                    <p class="product-name"><?php echo $product["p_name"]; ?></p>
                    <p class="product-category"><strong>Category:</strong> <?php echo htmlspecialchars($product_category_name); ?></p>
                    <p class="product-price">
                        <?php if ($discount_percent > 0): ?>
                            <span class="product-price-original">₹ <?php echo number_format($original_price, 2); ?></span>
                            <span class="product-price-final">₹ <?php echo number_format($discounted_price, 2); ?></span>
                            <span class="product-discount-badge"><?php echo number_format($discount_percent, 0); ?>% OFF</span>
                        <?php else: ?>
                            <span class="product-price-final">₹ <?php echo number_format($original_price, 2); ?></span>
                        <?php endif; ?>
                        <i> ( <?php echo $product["p_size"]; ?> )</i>
                    </p>
                    <p class="product-description"><?php echo $product["p_overview"]; ?></p>
                    <div class="product-features">
                        <h3>Key Features:</h3>
                        <ul>
                            <li><?php echo $product["p_f1"]; ?></li>
                            <li><?php echo $product["p_f2"]; ?></li>
                        </ul>
                    </div>
                    <div class="product-ingredients">
                        <h3>Key Ingredients:</h3>
                        <p><?php echo $product["p_ingred"]; ?></p>
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
                    <?php
                    if (isset($message)) {
                        echo '<div class="message">'.$message.'</div>';
                    }
                    if (isset($confirm)) {
                        foreach ($confirm as $conf) {
                            echo '<div class="confirm">'.$conf.'</div>';
                        }
                    }
                    if (isset($_SESSION['message'])) {
                        echo '<div class="message">'.$_SESSION['message'].'</div>';
                        unset($_SESSION['message']); 
                    }
                    ?>
                </div>
                <?php else: ?>
                <div class="product-details">
                    <p class="product-name">Product Not Found</p>
                    <p class="product-description">This product is not available right now.</p>
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
    <!-- footer -->
    <?php include('footer.php'); ?>
    <!-- footer -->
</body>
</html> 
