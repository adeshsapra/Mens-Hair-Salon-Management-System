Share


You said:
<?php
include 'connect.php';
session_start();
$user_id = $_SESSION['user_id'];

$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($product_id) {
    // Fetch the specific product details
    $pay_product = mysqli_query($con, "
        SELECT * 
        FROM products 
        WHERE p_id = '$product_id'");
    $fetch_pay_product = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $fetch_pay_product['p_price']; // Total for the specific product
} else {
    // Fetch total for all products in the cart
    $pay_product = mysqli_query($con, "
        SELECT SUM(c_total) AS grand_total 
        FROM product_cart 
        WHERE id = '$user_id'");
    $total_row = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $total_row['grand_total']; // Grand total for all products in the cart
}

if (isset($_POST['cod-btn'])) {
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    // Collect delivery details
    $fullName = mysqli_real_escape_string($con, $_POST['full-name']);
    $contactNumber = mysqli_real_escape_string($con, $_POST['contact-number']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $postalCode = mysqli_real_escape_string($con, $_POST['postal-code']);

    if ($product_id) {
        // Insert sale for the specific product
        $insertSale = mysqli_query($con, "
            INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time) 
            VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', 1, '{$fetch_pay_product['p_price']}', '{$pay_grand_total}', '$currentDate', 'Order Placed', '$currentTime')");

        if ($insertSale) {
            $s_id = mysqli_insert_id($con);
            // Insert payment record
            $insertPayment = mysqli_query($con, "
                INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status)
                VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'Cash On Delivery', '$currentDate', '$currentTime', 'Pending')");

            if ($insertPayment) {
                // Update product quantity
                $new_quantity = $fetch_pay_product['p_quantity'] - 1;
                if ($new_quantity >= 0) {
                    mysqli_query($con, "UPDATE products SET p_quantity = '$new_quantity' WHERE p_id = '$product_id'");
                    // Clear the cart
                    mysqli_query($con, "DELETE FROM product_cart WHERE id='$user_id' AND p_id='$product_id'");
                    echo "<script>alert('Order placed successfully!');</script>";
                    header('Location:thankyou_order.php');
                    exit();
                } else {
                    echo "<script>alert('Insufficient stock for the product.');</script>";
                }
            } else {
                echo "<script>alert('Failed to place order payment.');</script>";
            }
        } else {
            echo "<script>alert('Failed to place order.');</script>";
        }
    } else {
        // Insert sale for all products in the cart
        $all_products = mysqli_query($con, "
            SELECT product_cart.*, products.* 
            FROM product_cart 
            JOIN products ON product_cart.p_id = products.p_id 
            WHERE product_cart.id = '$user_id'");

        $insertSaleSuccess = true;

        while ($fetch_pay_product = mysqli_fetch_assoc($all_products)) {
            $insertSale = mysqli_query($con, "
                INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time) 
                VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', '{$fetch_pay_product['c_quantity']}', '{$fetch_pay_product['c_total']}', '{$pay_grand_total}', '$currentDate', 'Order Placed', '$currentTime')");

            if ($insertSale) {
                // Update product quantity after payment insertion
                $new_quantity = $fetch_pay_product['p_quantity'] - $fetch_pay_product['c_quantity'];
                if ($new_quantity >= 0) {
                    // Prepare to update product quantity after payment
                    $quantityUpdateQueries[] = "
                        UPDATE products 
                        SET p_quantity = '$new_quantity' 
                        WHERE p_id = '{$fetch_pay_product['p_id']}'";
                } else {
                    $insertSaleSuccess = false; 
                    echo "<script>alert('Insufficient stock for one or more products.');</script>";
                }
            } else {
                $insertSaleSuccess = false; // Mark failure if any insert fails
            }
        }

        // Insert payment record if all sales were successful
        if ($insertSaleSuccess) {
            $s_id = mysqli_insert_id($con);
            $insertPayment = mysqli_query($con, "
                INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status)
                VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'Cash On Delivery', '$currentDate', '$currentTime', 'Pending')");

            if ($insertPayment) {
                // Execute all product quantity updates
                foreach ($quantityUpdateQueries as $query) {
                    mysqli_query($con, $query);
                }
                mysqli_query($con, "DELETE FROM product_cart WHERE id='$user_id'");
                echo "<script>alert('Order placed successfully!');</script>";
                header('Location:thankyou_order.php');
                exit();
            } else {
                echo "<script>alert('Failed to place order payment.');</script>";
            }
        } else {
            echo "<script>alert('Failed to place order.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Payment</title>
    <link rel="stylesheet" href="user.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .payment-method { display: none; margin-top: 10px; }
        .payment-selection label { display: block; margin-bottom: 10px; cursor: pointer; }
        .payment-method.active { display: block; }
        .payment-options { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="payment-container">
        <header>
            <h1>Secure Checkout</h1>
            <p>Review your order, provide delivery details, and complete your payment</p>
        </header>

        <div class="payment-user-details">
            <div class="payment-content">
                <div class="payment-product-details">
                    <h2>Product Details</h2>
                    <?php
                    if ($product_id) {
                        // Display the single product details
                    ?>
                    <div class="payment-product-item">
                        <img src="../upload_product_photos/<?php echo $fetch_pay_product['p_img']; ?>" alt="Product Image">
                        <div class="payment-product-info">
                            <h3><?php echo $fetch_pay_product['p_name']; ?></h3>
                            <p><strong>Price:</strong> ₹ <?php echo $fetch_pay_product['p_price']; ?></p>
                            <p><strong>Size:</strong> <?php echo $fetch_pay_product['p_size']; ?></p>
                            <p><strong>Quantity:</strong> 1</p>
                            <p><strong>Total:</strong> ₹ <?php echo $fetch_pay_product['p_price']; ?></p>
                        </div>
                    </div>
                    <?php
                    } else {
                        // Display all products in cart
                        $all_products = mysqli_query($con, "
                            SELECT product_cart.*, products.* 
                            FROM product_cart 
                            JOIN products ON product_cart.p_id = products.p_id 
                            WHERE product_cart.id = '$user_id'");
                        while ($fetch_pay_product = mysqli_fetch_assoc($all_products)) {
                    ?>
                    <div class="payment-product-item">
                        <img src="../upload_product_photos/<?php echo $fetch_pay_product['p_img']; ?>" alt="Product Image">
                        <div class="payment-product-info">
                            <h3><?php echo $fetch_pay_product['p_name']; ?></h3>
                            <p><strong>Price:</strong> ₹ <?php echo $fetch_pay_product['p_price']; ?></p>
                            <p><strong>Size:</strong> <?php echo $fetch_pay_product['p_size']; ?></p>
                            <p><strong>Quantity:</strong> <?php echo $fetch_pay_product['c_quantity']; ?></p>
                            <p><strong>Total:</strong> ₹ <?php echo $fetch_pay_product['c_total']; ?></p>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    ?>
                    <div class="payment-total">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Total Amount</td>
                                    <td>₹ <?php echo $pay_grand_total; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h2>Delivery Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="full-name">Full Name:</label>
                        <input type="text" id="full-name" name="full-name" placeholder="Enter your full name" required>

                        <label for="contact-number">Contact Number:</label>
                        <input type="text" id="contact-number" name="contact-number" placeholder="Enter your contact number" required>

                        <label for="address">Address:</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your delivery address" required></textarea>

                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" placeholder="Enter your city" required>

                        <label for="state">State:</label>
                        <input type="text" id="state" name="state" placeholder="Enter your State" required>

                        <label for="postal-code">Pin Code:</label>
                        <input type="text" id="postal-code" name="postal-code" placeholder="Enter postal code" required>
                    </div>

                    <div class="payment-options">
                        <h2>Payment Options</h2>
                        <div class="payment-selection">
                            <label>
                                <input type="radio" name="payment-method" value="upi" onclick="showPaymentOption('upi')"> UPI
                            </label>
                            <div class="payment-method" id="upi">
                                <h3>UPI</h3>
                                <input type="text" placeholder="Enter UPI ID">
                                <button class="payments_buttons">Pay with UPI</button>
                            </div>
                            
                            <label>
                                <input type="radio" name="payment-method" value="card" onclick="showPaymentOption('card')"> Credit/Debit Card
                            </label>
                            <div class="payment-method" id="card">
                                <h3>Credit/Debit Card</h3>
                                <input type="text" placeholder="Card Number">
                                <input type="text" placeholder="MM/YY" maxlength="5" pattern="(0[1-9]|1[0-2])\/[0-9]{2}">
                                <input type="text" placeholder="CVV" maxlength="3" minlength="3" pattern="\d{3}">
                                <button class="payments_buttons">Pay with Card</button>
                            </div>
                            
                            <label>
                                <input type="radio" name="payment-method" value="cod" onclick="showPaymentOption('cod')"> Cash on Delivery (COD)
                            </label>
                            <div class="payment-method" id="cod">
                                <h3>Cash on Delivery (COD)</h3>
                                <p>Select COD if you prefer to pay with cash upon delivery.</p>
                                <button name="cod-btn" class="payments_buttons">Confirm Order</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to hide all payment options
        function hideAllPaymentOptions() {
            document.querySelectorAll('.payment-method').forEach(function (el) {
                el.classList.remove('active');
            });
        }

        // Function to show the selected payment option
        function showPaymentOption(option) {
            hideAllPaymentOptions();
            document.getElementById(option).classList.add('active');
        }
    </script>
</body>
</html>