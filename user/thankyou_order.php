<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed Successfully</title>
    <link rel="stylesheet" href="user.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="thankyou-page">
    <div class="thankyou-wrapper">
        <div class="thankyou-card">
            <div class="thankyou-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Order Placed Successfully</h1>
            <p>Your order is confirmed. We are preparing it for dispatch.</p>

            <div class="thankyou-meta">
                <span><i class="fas fa-shield-alt"></i> Secure payment status recorded</span>
                <span><i class="fas fa-clock"></i> Redirecting to your orders in <strong id="countdown">5</strong>s</span>
            </div>

            <div class="thankyou-actions">
                <a href="order.php" class="thankyou-btn primary"><i class="fas fa-clipboard-list"></i> View Orders</a>
                <a href="../eshop.php" class="thankyou-btn secondary"><i class="fas fa-shopping-basket"></i> Continue Shopping</a>
            </div>
        </div>
    </div>

    <script>
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(function () {
            seconds -= 1;
            if (countdownEl) countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = 'order.php';
            }
        }, 1000);
    </script>
</body>
</html>
