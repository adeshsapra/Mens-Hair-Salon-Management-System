<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

function getDiscountedPrice($price, $discountPercent) {
    if (empty($price)) return 0;
    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$pay_grand_total = 0;
$fetch_pay_product = null;

if ($product_id) {
    $pay_product = mysqli_query($con, "SELECT * FROM products WHERE p_id = '$product_id'");
    $fetch_pay_product = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $fetch_pay_product ? getDiscountedPrice($fetch_pay_product['p_price'], $fetch_pay_product['p_discount'] ?? 0) : 0;
} else {
    $pay_product = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '$user_id'");
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
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Product not found.';
            header('Location: products_user.php');
            exit();
        } else {
            $discounted_unit_price = getDiscountedPrice($fetch_pay_product['p_price'], $fetch_pay_product['p_discount'] ?? 0);
            $insertSale = mysqli_query($con, "
                INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
                VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', 1, '{$discounted_unit_price}', '{$pay_grand_total}', '$currentDate', 'pending', '$currentTime')");

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
                        $_SESSION['toast-type'] = 'success';
                        $_SESSION['toast-msg'] = 'Order placed successfully!';
                        header('Location:thankyou_order.php');
                        exit();
                    } else {
                         $_SESSION['toast-type'] = 'error';
                         $_SESSION['toast-msg'] = 'Insufficient stock for the product.';
                    }
                } else {
                    $_SESSION['toast-type'] = 'error';
                    $_SESSION['toast-msg'] = 'Failed to place order payment.';
                }
            } else {
                $_SESSION['toast-type'] = 'error';
                $_SESSION['toast-msg'] = 'Failed to place order.';
            }
        }
    } else {
        $all_products = mysqli_query($con, "
            SELECT product_cart.*, products.*
            FROM product_cart
            JOIN products ON product_cart.p_id = products.p_id
            WHERE product_cart.id = '$user_id'");

        $insertSaleSuccess = true;
        $quantityUpdateQueries = [];

        while ($p = mysqli_fetch_assoc($all_products)) {
            $insertSale = mysqli_query($con, "
                INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
                VALUES ('$user_id', '{$p['p_img']}', '{$p['p_name']}', '{$p['p_price']}', '{$p['p_size']}', '{$p['c_quantity']}', '{$p['c_total']}', '{$pay_grand_total}', '$currentDate', 'pending', '$currentTime')");

            if ($insertSale) {
                $new_quantity = $p['p_quantity'] - $p['c_quantity'];
                if ($new_quantity >= 0) {
                    $quantityUpdateQueries[] = "UPDATE products SET p_quantity = '$new_quantity' WHERE p_id = '{$p['p_id']}'";
                } else {
                    $insertSaleSuccess = false;
                    $_SESSION['toast-type'] = 'error';
                    $_SESSION['toast-msg'] = 'Insufficient stock for one or more products.';
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
                $_SESSION['toast-type'] = 'success';
                $_SESSION['toast-msg'] = 'Order placed successfully!';
                header('Location:thankyou_order.php');
                exit();
            } else {
                 $_SESSION['toast-type'] = 'error';
                 $_SESSION['toast-msg'] = 'Failed to place order payment.';
            }
        } else {
             $_SESSION['toast-type'] = 'error';
             $_SESSION['toast-msg'] = 'Failed to place order.';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        #global-toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 100000; display: flex; flex-direction: column; gap: 10px; }
        .global-toast { min-width: 250px; background: #333; color: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500; transform: translateX(120%); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .global-toast.show { transform: translateX(0); }
        .toast-success { background: #10b981; border-left: 5px solid #059669; }
        .toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
    </style>
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
                            <div id="card-element"></div>
                            <div id="card-errors" role="alert"></div>
                            <button type="button" id="stripe-submit-btn" class="payments_buttons">Pay Now with Stripe</button>
                        </div>
                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="cod" onclick="showPaymentOption('cod')"> Cash on Delivery (COD)
                        </label>
                        <div class="payment-method" id="cod">
                            <h3>Cash on Delivery (COD)</h3>
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
                                $single_original = (float) $fetch_pay_product['p_price'];
                                $single_discount = (float) ($fetch_pay_product['p_discount'] ?? 0);
                                $single_final = getDiscountedPrice($single_original, $single_discount);
                            ?>
                            <div class="payment-product-item">
                                <img src="../upload_product_photos/<?php echo $fetch_pay_product['p_img']; ?>" alt="Product">
                                <div class="payment-product-info">
                                    <h3><?php echo $fetch_pay_product['p_name']; ?></h3>
                                    <?php if ($single_discount > 0): ?>
                                        <p>Final Price: ₹ <?php echo number_format($single_final, 2); ?></p>
                                    <?php else: ?>
                                        <p>Price: ₹ <?php echo number_format($single_original, 2); ?></p>
                                    <?php endif; ?>
                                    <p>Quantity: 1</p>
                                </div>
                            </div>
                            <?php
                            }
                        } else {
                            $all_products = mysqli_query($con, "
                                SELECT product_cart.*, products.*
                                FROM product_cart
                                JOIN products ON product_cart.p_id = products.p_id
                                WHERE product_cart.id = '$user_id'");
                            while ($p = mysqli_fetch_assoc($all_products)) {
                            ?>
                            <div class="payment-product-item">
                                <img src="../upload_product_photos/<?php echo $p['p_img']; ?>" alt="Product">
                                <div class="payment-product-info">
                                    <h3><?php echo $p['p_name']; ?></h3>
                                    <p>Total: ₹ <?php echo number_format((float) $p['c_total']); ?></p>
                                </div>
                            </div>
                            <?php
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

    <div id="global-toast-container"></div>

    <?php require_once '../stripe_config.php'; ?>
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('global-toast-container');
            const toast = document.createElement('div');
            toast.className = `global-toast toast-${type}`;
            let icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
        }

        <?php if (isset($_SESSION['toast-msg'])): ?>
            showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo $_SESSION['toast-type'] ?? 'success'; ?>");
            <?php unset($_SESSION['toast-msg'], $_SESSION['toast-type']); ?>
        <?php endif; ?>

        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        function showPaymentOption(option) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            document.getElementById(option).classList.add('active');
        }

        document.getElementById('stripe-submit-btn').addEventListener('click', async function() {
            const btn = this;
            const fullName = document.getElementById('full-name').value;
            if (!fullName) { showToast('Please fill in all details.', 'error'); return; }

            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            const response = await fetch('create_payment_intent.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=<?php echo $product_id ?: ""; ?>'
            });
            const data = await response.json();
            
            if (data.error) { showToast(data.error, 'error'); btn.disabled = false; return; }

            const { paymentIntent, error } = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: { card: card, billing_details: { name: fullName } }
            });

            if (error) { document.getElementById('card-errors').textContent = error.message; btn.disabled = false; }
            else if (paymentIntent.status === 'succeeded') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'handle_stripe_payment.php';
                const fields = { 'payment_intent_id': paymentIntent.id, 'full-name': fullName, 'contact-number': document.getElementById('contact-number').value, 'address': document.getElementById('address').value, 'city': document.getElementById('city').value, 'state': document.getElementById('state').value, 'postal-code': document.getElementById('postal-code').value, 'product_id': '<?php echo $product_id ?: ""; ?>' };
                for (const key in fields) { const input = document.createElement('input'); input.type = 'hidden'; input.name = key; input.value = fields[key]; form.appendChild(input); }
                document.body.appendChild(form);
                form.submit();
            }
        });
        document.addEventListener('DOMContentLoaded', () => showPaymentOption('stripe'));
    </script>
</body>
</html>
