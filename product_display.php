<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .cart-icon {
            position: absolute;
            right: 50px;
            font-size: 24px;
            color: #000;
            display: flex;
            align-items: center;
            cursor: pointer; /* Change cursor to pointer */
        }
        .cart-count {
            position: absolute;
            left: 20px;
            bottom: 20px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 0 5px;
            margin-left: 5px;
            font-size: 12px;
        }
        .message {
            color: red;
            font-weight: bold;
        }
        .confirm {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- header -->
    <?php 
    include('header.php'); 
    ?>
    <!-- header -->
    <div class="main-product-show">
        <div class="product-display-main">
            <div class="product-display-container">

            <?php
            include('connect.php');
            $user_id = $_SESSION['user_id'] ?? null;

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $query = "SELECT * FROM products WHERE p_id = $id";
            $all_product = $con->query($query);
            $product = mysqli_fetch_assoc($all_product);

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
                        $c_price = $product['p_price'];
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
                } elseif ($product['p_quantity'] <= 0) {
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
                $item_count = $count_data['item_count'];
            }
            ?>

                <img src="upload_product_photos/<?php echo $product["p_img"]; ?>" alt="Product Image" class="product-image">
                <div class="product-details">
                    <p class="product-name"><?php echo $product["p_name"]; ?></p>
                    <p class="product-price">₹ <?php echo $product["p_price"]; ?> <i> ( <?php echo $product["p_size"]; ?> )</i></p>
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
                        <a href="eshop.php" class="continue-shopping">Continue E-shop</a>
                        <form action="" method="post" style="display:inline;">
                            <button type="submit" name="add_to_cart" class="view-cart">Add to Cart</button>
                        </form>
                        <form action="" method="post" style="display:inline;">
                            <button type="submit" name="buy_now" class="view-cart">Buy Now</button>
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
                <a href="<?php echo $user_id ? 'user/products_user.php' : 'javascript:void(0);'; ?>" class="cart-icon" 
                   onclick="<?php if (!$user_id) echo 'alert(\'Sorry..! You are not logged in.\');'; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo isset($item_count) ? $item_count : 0; ?></span>
                </a>
            </div>
        </div>
    </div>
    <!-- footer -->
    <?php include('footer.php'); ?>
    <!-- footer -->
</body>
</html> 