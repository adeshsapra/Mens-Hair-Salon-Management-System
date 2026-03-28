<?php
include 'connect.php';
session_start();
$user_id = $_SESSION['user_id'];

$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$pay_grand_total = 0;
$fetch_pay_product = null;
$quantityUpdateQueries = [];

if ($product_id) {
    $pay_product = mysqli_query($con, "
        SELECT *
        FROM products
        WHERE p_id = '$product_id'");
    $fetch_pay_product = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $fetch_pay_product ? $fetch_pay_product['p_price'] : 0;
} else {
    $pay_product = mysqli_query($con, "
        SELECT SUM(c_total) AS grand_total
        FROM product_cart
        WHERE id = '$user_id'");
    $total_row = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $total_row && $total_row['grand_total'] ? $total_row['grand_total'] : 0;
}

if (isset($_POST['cod-btn'])) {
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    $fullName = mysqli_real_escape_string($con, $_POST['full-name']);
    $contactNumber = mysqli_real_escape_string($con, $_POST['contact-number']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $postalCode = mysqli_real_escape_string($con, $_POST['postal-code']);

    if ($product_id) {
        if (!$fetch_pay_product) {
            echo "<script>alert('Product not found.');</script>";
        } else {
            $insertSale = mysqli_query($con, "
                INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
                VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', 1, '{$fetch_pay_product['p_price']}', '{$pay_grand_total}', '$currentDate', 'pending', '$currentTime')");

            if ($insertSale) {
                $s_id = mysqli_insert_id($con);
                $insertPayment = mysqli_query($con, "
                    INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status)
                    VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'cod', '$currentDate', '$currentTime', 'pending')");

                if ($insertPayment) {
                    mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ('$s_id', 'pending', '$currentDate', '$currentTime')");
                    $new_quantity = $fetch_pay_product['p_quantity'] - 1;
                    if ($new_quantity >= 0) {
                        mysqli_query($con, "UPDATE products SET p_quantity = '$new_quantity' WHERE p_id = '$product_id'");
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
        }
    } else {
        $all_products = mysqli_query($con, "
            SELECT product_cart.*, products.*
            FROM product_cart
            JOIN products ON product_cart.p_id = products.p_id
            WHERE product_cart.id = '$user_id'");

        $insertSaleSuccess = true;

        while ($fetch_pay_product = mysqli_fetch_assoc($all_products)) {
            $insertSale = mysqli_query($con, "
                INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
                VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', '{$fetch_pay_product['c_quantity']}', '{$fetch_pay_product['c_total']}', '{$pay_grand_total}', '$currentDate', 'pending', '$currentTime')");

            if ($insertSale) {
                $new_quantity = $fetch_pay_product['p_quantity'] - $fetch_pay_product['c_quantity'];
                if ($new_quantity >= 0) {
                    $quantityUpdateQueries[] = "
                        UPDATE products
                        SET p_quantity = '$new_quantity'
                        WHERE p_id = '{$fetch_pay_product['p_id']}'";
                } else {
                    $insertSaleSuccess = false;
                    echo "<script>alert('Insufficient stock for one or more products.');</script>";
                }
            } else {
                $insertSaleSuccess = false;
            }
        }

        if ($insertSaleSuccess) {
            $s_id = mysqli_insert_id($con);
            $insertPayment = mysqli_query($con, "
                INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status)
                VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'cod', '$currentDate', '$currentTime', 'pending')");

            if ($insertPayment) {
                mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ('$s_id', 'pending', '$currentDate', '$currentTime')");
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
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="checkout-page">
    <div class="payment-container">
        <header class="checkout-header">
            <h1>Secure Checkout</h1>
            <p>Complete your delivery details and choose payment method to place your order.</p>
        </header>

        <form method="POST" class="checkout-grid">
            <section class="checkout-left">
                <div class="checkout-card">
                    <h2>Delivery Details</h2>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="full-name">Full Name</label>
                            <input type="text" id="full-name" name="full-name" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-field">
                            <label for="contact-number">Contact Number</label>
                            <input type="text" id="contact-number" name="contact-number" placeholder="Enter your contact number" required>
                        </div>

                        <div class="form-field full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Enter your delivery address" required></textarea>
                        </div>

                        <div class="form-field">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" placeholder="Enter your city" required>
                        </div>

                        <div class="form-field">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" placeholder="Enter your state" required>
                        </div>

                        <div class="form-field">
                            <label for="postal-code">Pin Code</label>
                            <input type="text" id="postal-code" name="postal-code" placeholder="Enter postal code" required>
                        </div>
                    </div>
                </div>

                <div class="checkout-card payment-options">
                    <h2>Payment Method</h2>
                    <div class="payment-selection">
                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="stripe" onclick="showPaymentOption('stripe')"> Stripe
                        </label>
                        <div class="payment-method" id="stripe">
                            <h3>Pay with Stripe</h3>
                            <p>Secure payment using Stripe.</p>
                            <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                            <!-- Used to display form errors. -->
                            <div id="card-errors" role="alert"></div>
                            <button type="button" id="stripe-submit-btn" class="payments_buttons">Pay Now with Stripe</button>
                        </div>

                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="cod" onclick="showPaymentOption('cod')"> Cash on Delivery (COD)
                        </label>
                        <div class="payment-method" id="cod">
                            <h3>Cash on Delivery (COD)</h3>
                            <p>Pay in cash when your order is delivered to your address.</p>
                            <button type="submit" name="cod-btn" class="payments_buttons">Confirm Order</button>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="checkout-right">
                <div class="checkout-card">
                    <h2>Order Summary</h2>
                    <div class="order-list">
                        <?php
                        if ($product_id) {
                            if ($fetch_pay_product) {
                            ?>
                            <div class="payment-product-item">
                                <img src="../upload_product_photos/<?php echo $fetch_pay_product['p_img']; ?>" alt="Product Image">
                                <div class="payment-product-info">
                                    <h3><?php echo $fetch_pay_product['p_name']; ?></h3>
                                    <p>Price: ₹ <?php echo number_format((float) $fetch_pay_product['p_price']); ?></p>
                                    <p>Size: <?php echo $fetch_pay_product['p_size']; ?></p>
                                    <p>Quantity: 1</p>
                                    <p>Total: ₹ <?php echo number_format((float) $fetch_pay_product['p_price']); ?></p>
                                </div>
                            </div>
                            <?php
                            } else {
                                echo '<p class="empty-order">No product details available for this checkout.</p>';
                            }
                        } else {
                            $all_products = mysqli_query($con, "
                                SELECT product_cart.*, products.*
                                FROM product_cart
                                JOIN products ON product_cart.p_id = products.p_id
                                WHERE product_cart.id = '$user_id'");

                            if (mysqli_num_rows($all_products) > 0) {
                                while ($fetch_pay_product = mysqli_fetch_assoc($all_products)) {
                                ?>
                                <div class="payment-product-item">
                                    <img src="../upload_product_photos/<?php echo $fetch_pay_product['p_img']; ?>" alt="Product Image">
                                    <div class="payment-product-info">
                                        <h3><?php echo $fetch_pay_product['p_name']; ?></h3>
                                        <p>Price: ₹ <?php echo number_format((float) $fetch_pay_product['p_price']); ?></p>
                                        <p>Size: <?php echo $fetch_pay_product['p_size']; ?></p>
                                        <p>Quantity: <?php echo $fetch_pay_product['c_quantity']; ?></p>
                                        <p>Total: ₹ <?php echo number_format((float) $fetch_pay_product['c_total']); ?></p>
                                    </div>
                                </div>
                                <?php
                                }
                            } else {
                                echo '<p class="empty-order">Your cart is empty.</p>';
                            }
                        }
                        ?>
                    </div>

                    <div class="payment-total">
                        <span>Total Amount</span>
                        <strong>₹ <?php echo number_format((float) $pay_grand_total); ?></strong>
                    </div>
                </div>
            </aside>
        </form>
    </div>

    <?php require_once '../stripe_config.php'; ?>
    <script>
        // Stripe Configuration
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const elements = stripe.elements();
        const rootStyles = getComputedStyle(document.documentElement);
        const brandColor = rootStyles.getPropertyValue('--brand').trim() || '#cbb90f';
        const bgColor = rootStyles.getPropertyValue('--bg1').trim() || '#18150d';

        // Custom styling can be passed to options when creating an Element.
        const style = {
            base: {
                color: bgColor,
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: brandColor
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        const card = elements.create('card', {style: style});
        card.mount('#card-element');

        card.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        function hideAllPaymentOptions() {
            document.querySelectorAll('.payment-method').forEach(function (el) {
                el.classList.remove('active');
            });
        }

        function showPaymentOption(option) {
            hideAllPaymentOptions();
            var selectedOption = document.getElementById(option);
            if (selectedOption) {
                selectedOption.classList.add('active');
            }
        }

        document.getElementById('stripe-submit-btn').addEventListener('click', async function(e) {
            const btn = this;
            const fullName = document.getElementById('full-name').value;
            const contactNumber = document.getElementById('contact-number').value;
            const address = document.getElementById('address').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const postalCode = document.getElementById('postal-code').value;

            if (!fullName || !contactNumber || !address || !city || !state || !postalCode) {
                alert('Please fill in all delivery details first.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            try {
                const response = await fetch('create_payment_intent.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=<?php echo $product_id ?: ""; ?>'
                });
                
                const data = await response.json();
                
                if (data.error) {
                    alert(data.error);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now with Stripe';
                    return;
                }

                const result = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: fullName,
                            phone: contactNumber,
                            address: {
                                line1: address,
                                city: city,
                                state: state,
                                postal_code: postalCode
                            }
                        }
                    }
                });

                if (result.error) {
                    document.getElementById('card-errors').textContent = result.error.message;
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now with Stripe';
                } else {
                    if (result.paymentIntent.status === 'succeeded') {
                        // Payment successful, submit to server
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'handle_stripe_payment.php';

                        const fields = {
                            'payment_intent_id': result.paymentIntent.id,
                            'full-name': fullName,
                            'contact-number': contactNumber,
                            'address': address,
                            'city': city,
                            'state': state,
                            'postal-code': postalCode,
                            'product_id': '<?php echo $product_id ?: ""; ?>'
                        };

                        for (const key in fields) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key];
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = 'Pay Now with Stripe';
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            var defaultOption = document.querySelector('input[name="payment-method"][value="stripe"]');
            if (defaultOption) {
                defaultOption.checked = true;
                showPaymentOption('stripe');
            }
        });
    </script>
</body>
</html>

