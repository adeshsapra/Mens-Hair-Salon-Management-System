<?php
include('connect.php');
session_start();

if ($con) {
    $tbl = mysqli_query($con, "SHOW TABLES LIKE 'membership_plans'");
    if ($tbl && mysqli_num_rows($tbl) > 0) {
        $fc = mysqli_query($con, "SHOW COLUMNS FROM membership_plans LIKE 'is_featured'");
        if ($fc && mysqli_num_rows($fc) === 0) {
            mysqli_query(
                $con,
                'ALTER TABLE membership_plans ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER features_json'
            );
        }
    }
}

function membership_public_default_plans(): array
{
    $keys = ['royal', 'classic', 'standard'];
    $names = ['Royal Pass', 'Classic Pass', 'Standard Pass'];
    $out = [];
    foreach ($keys as $i => $pk) {
        $out[$pk] = [
            'yearly' => ['features' => [], 'price' => 0, 'display_name' => $names[$i], 'is_featured' => false],
            'monthly' => ['features' => [], 'price' => 0, 'display_name' => $names[$i], 'is_featured' => false],
        ];
    }
    return $out;
}

function membership_public_load_plans(mysqli $con): array
{
    $plans = membership_public_default_plans();
    $res = mysqli_query($con, 'SELECT pass_key, billing_plan, display_name, price, features_json, is_featured FROM membership_plans');
    if (!$res) {
        return $plans;
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $pk = $row['pass_key'];
        $bp = $row['billing_plan'];
        if (!isset($plans[$pk][$bp])) {
            continue;
        }
        $feat = json_decode($row['features_json'], true);
        $plans[$pk][$bp]['features'] = is_array($feat) ? $feat : [];
        $plans[$pk][$bp]['price'] = (int) $row['price'];
        $dn = trim((string) $row['display_name']);
        if ($dn !== '') {
            $plans[$pk][$bp]['display_name'] = $dn;
        }
        $plans[$pk][$bp]['is_featured'] = (int) ($row['is_featured'] ?? 0) === 1;
    }
    return $plans;
}

$plans = membership_public_load_plans($con);
$membership_pass_order = ['royal', 'classic', 'standard'];
$membership_taglines = [
    'royal' => 'Premium perks, priority booking & exclusive member treats',
    'classic' => 'Balanced savings for your regular salon routine',
    'standard' => 'Essential discounts and member-only access',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($user_id <= 0) {
        header('Location: login.php');
        exit();
    }
    $membership_type = mysqli_real_escape_string($con, (string) ($_POST['membership_type'] ?? ''));
    $price = mysqli_real_escape_string($con, (string) ($_POST['price'] ?? ''));
    $card_name = mysqli_real_escape_string($con, (string) ($_POST['card-name'] ?? ''));
    $phone_number = mysqli_real_escape_string($con, (string) ($_POST['phone-number'] ?? ''));
    $payment_date = date('Y-m-d H:i:s');

    $query = "INSERT INTO membership_payments (id, membership_type, price, card_name, phone_number, payment_date, status) 
              VALUES ('$user_id', '$membership_type', '$price', '$card_name', '$phone_number', '$payment_date', 'active')";

    if (mysqli_query($con, $query)) {
        header('Location: thankyou_membership.php');
        exit();
    }
    echo 'Error: ' . mysqli_error($con);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut MemberShip</title>

    <!-- font awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
     <!-- box link -->
     <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
     
     :root {
    --bg1: #18150d; /* Dark background */
    --bg2: #eae3c2; /* Light background */
    --body: #a39623; /* Body color */
    --brand: #cbb90f; /* Brand color */
    --white: #fff; /* White color */
        }

        .payment-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); 
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease; 
        }

        .popup-content {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            width: 500px;
            max-width: 90%;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: var(--bg1); 
        }

        form {
            display: flex;
            flex-direction: column;
        }

        form label {
            margin-top: 15px;
            color: var(--bg1); 
        }

        form input {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid var(--body); 
            border-radius: 5px;
        }

        .pay-submit-btn {
            background-color: var(--body); 
            color: var(--white); 
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            margin-left:0;
            transition: background-color 0.3s, color 0.3s;
        }

        .pay-submit-btn:hover {
            background-color: var(--bg1);
            color: var(--brand); 
        }

        .hidden {
            display: none;
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .fade-out {
            animation: fadeOut 0.3s ease forwards; /* Fade-out animation */
        }

</style>
</head>
<body>

    <!-- header and navigation section -->

    <header class="header">

        <a href="index.php" class="logo">
            <img src="photos/logoo.png" alt="ClassyCut">
        </a>
        <nav class="menu">
            <a href="index.php">Home</a>
            <a href="service.php">Services</a>
            <a href="eshop.php">E-shop</a>
            <a href="membership.php">Membership</a>
            <!-- <a href="appointment.php">Appointment</a> -->
            <?php
            // session_start();
            if (isset($_SESSION['user_id'])) {
                echo '<a href="appointment.php">Appointment</a>';
            }
        ?>
        </nav>
        <div class="icons">
             <div class="fas fa-search" id="search-btn"></div>
             <div class="fas fa-bars" id="menu-btn"></div>
        </div>
        <div class="search-form">
            <input type="search" name="search" id="search-box" placeholder="Search Here....">
            <label for="search-box" class="fas fa-search"></label>
        </div>
        <?php
            //session_start();
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $query = "SELECT username FROM user_reg WHERE id = '$user_id'";
                $result = mysqli_query($con, $query);
                $row = mysqli_fetch_assoc($result);
    
                $username = $row['username'];
                echo '<div class="user-profile">';
                echo '<a href="user/index.php"><i class="fas fa-user-circle"></i></a>'; 
                echo '<a href="user/index.php" class="username">' . $username . '</a>';
                echo '</div>';
            } else {
                echo '<div class="login">';
                echo '<a href="login.php">Sign-In</a>';
                echo '</div>';
            }
        ?>
        <!-- <div class="login">
            <a href="login.php">Sign-In</a>
        </div>
        -->
    </header>

     <!-- header and navigation section -->

    
    <!-- defualt section -->  
    <div class="defualt-section">
        <img src="photos/about-img1.jpeg" alt="" class="img">
        <div class="img-content">
            <h2>VIP Exclusive Membership</h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <span>Our Membership Page</span>
            </div>
           
        </div>
        
     </div>


    <!-- defualt section -->

    <!-- membership section -->

    <div class="membership-container membership-pricing">
        <h1 class="membership-pricing-title">Choose your plan</h1>
        <p class="membership-pricing-lead">Pick yearly or monthly billing. Change your mind anytime.</p>
        <div class="membership-billing-toggle toggle-buttons" role="group" aria-label="Billing period">
            <button type="button" id="yearly-btn" class="membership-toggle-btn active">Annual</button>
            <button type="button" id="monthly-btn" class="membership-toggle-btn">Monthly</button>
        </div>
        <div id="subscriptions" class="membership-pricing-grid-wrap">
            <?php
            $membership_periods = [
                'yearly' => ['pane_id' => 'yearly-cards', 'pane_class' => 'membership-pricing-cards', 'suffix' => 'year', 'period_label' => 'Yearly'],
                'monthly' => ['pane_id' => 'monthly-cards', 'pane_class' => 'membership-pricing-cards membership-pricing-cards--hidden', 'suffix' => 'month', 'period_label' => 'Monthly'],
            ];
            foreach ($membership_periods as $period => $pane):
                ?>
            <div id="<?php echo htmlspecialchars($pane['pane_id']); ?>" class="<?php echo htmlspecialchars($pane['pane_class']); ?>">
                <?php foreach ($membership_pass_order as $pk):
                    $slot = $plans[$pk][$period];
                    $checkout_name = $pane['period_label'] . ' ' . $slot['display_name'];
                    $price = (int) $slot['price'];
                    $featured = !empty($slot['is_featured']);
                    $card_classes = 'membership-card' . ($featured ? ' membership-card--featured' : '');
                    $tagline = $membership_taglines[$pk] ?? '';
                    $cta_class = $featured ? 'membership-card-cta membership-card-cta--primary' : 'membership-card-cta membership-card-cta--ghost';
                    ?>
                <article class="<?php echo htmlspecialchars($card_classes); ?>">
                    <?php if ($featured): ?>
                        <span class="membership-card-badge"><span class="membership-card-badge-dot" aria-hidden="true"></span> Most popular</span>
                    <?php endif; ?>
                    <h2 class="membership-card-name"><?php echo htmlspecialchars($slot['display_name']); ?></h2>
                    <div class="membership-card-price-row">
                        <span class="membership-card-currency">₹</span>
                        <span class="membership-card-amount-digits"><?php echo number_format($price, 0); ?></span>
                        <span class="membership-card-period">/<?php echo htmlspecialchars($pane['suffix']); ?></span>
                    </div>
                    <?php if ($tagline !== ''): ?>
                        <p class="membership-card-tagline"><?php echo htmlspecialchars($tagline); ?></p>
                    <?php endif; ?>
                    <ul class="membership-card-list">
                        <?php foreach ($slot['features'] as $desc): ?>
                            <li><?php echo htmlspecialchars($desc); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="<?php echo htmlspecialchars($cta_class); ?>" data-checkout-name="<?php echo htmlspecialchars($checkout_name); ?>">Subscribe</button>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- membership section -->
<!-- Payment Popup --><!-- Payment Popup --><div id="payment-popup" class="payment-popup hidden">
    <div class="popup-content">
        <span class="close-btn">&times;</span>
        <h2 id="membership-title">Membership Payment</h2>
        <p>You're subscribing to: <span id="membership-name"></span></p>
        <p>Price: ₹ <span id="membership-price"></span></p>

        <form id="payment-form" method="POST">
            <input type="hidden" name="membership_type" id="membership-type">
            <input type="hidden" name="price" id="membership-price-hidden">
            
            <label for="card-name">Name on Card:</label>
            <input type="text" id="card-name" name="card-name" required>
            
            <label for="phone-number">Phone Number:</label>
            <input type="tel" id="phone-number" name="phone-number" maxlength="10" placeholder="10 Phone Number" required>
            <span id="phone-number-error" class="error-message"></span>
            
            <label for="card-number">Card Number:</label>
            <input type="text" id="card-number" name="card-number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>
            <span id="card-number-error" class="error-message"></span>
            
            <label for="expiry-date">Expiry Date:</label>
            <input type="text" id="expiry-date" name="expiry-date" maxlength="5" placeholder="MM/YY" required>
            
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" maxlength="3" placeholder="XXX" required>
            
            <button type="submit" class="pay-submit-btn" id="payment-btn">Complete Payment</button>
        </form>

    </div>
</div>


    <script>


        // Script for opening and closing the popup
const paymentPopup = document.getElementById('payment-popup');
const closeBtn = document.querySelector('.close-btn');
const membershipNameElement = document.getElementById('membership-name');
const membershipPriceElement = document.getElementById('membership-price');

function formatInrDigits(priceDigits) {
    return priceDigits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function openPopup(passName, priceDigits) {
    if (!paymentPopup || !membershipNameElement || !membershipPriceElement) return;
    membershipNameElement.textContent = passName;
    const pretty = formatInrDigits(priceDigits);
    membershipPriceElement.textContent = pretty;
    const typeInput = document.getElementById('membership-type');
    const priceInput = document.getElementById('membership-price-hidden');
    const paymentButton = document.getElementById('payment-btn');
    if (typeInput) typeInput.value = passName;
    if (priceInput) priceInput.value = priceDigits;
    if (paymentButton) paymentButton.textContent = `Pay ₹ ${pretty}`;
    paymentPopup.classList.remove('hidden');
}

// Close the popup when clicking outside of it
window.addEventListener('click', function (event) {
    if (paymentPopup && event.target === paymentPopup) {
        paymentPopup.classList.add('hidden');
    }
});


if (closeBtn && paymentPopup) {
    closeBtn.addEventListener('click', () => {
        paymentPopup.classList.add('hidden');
    });
}

document.querySelectorAll('.membership-card-cta').forEach(button => {
    button.addEventListener('click', function() {
        const card = this.closest('.membership-card');
        const passName = this.getAttribute('data-checkout-name') || (card && card.querySelector('.membership-card-name') && card.querySelector('.membership-card-name').textContent.trim());
        const digitsEl = card && card.querySelector('.membership-card-amount-digits');
        if (!passName || !digitsEl) return;
        const priceDigits = digitsEl.textContent.replace(/,/g, '').replace(/\D/g, '');
        if (priceDigits === '') return;
        openPopup(passName, priceDigits);
    });
});
const form = document.getElementById('payment-form');
const cardNumberInput = document.getElementById('card-number');
const cardNumberError = document.getElementById('card-number-error');
const phoneNumberInput = document.getElementById('phone-number');
const phoneNumberError = document.getElementById('phone-number-error');
const expiryDateInput = document.getElementById('expiry-date');
const cvvInput = document.getElementById('cvv');

if (cardNumberInput && cardNumberError) {
    cardNumberInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '').slice(0, 16);
        value = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = value;
        cardNumberError.textContent = '';
    });
}

if (phoneNumberInput && phoneNumberError) {
    phoneNumberInput.addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        phoneNumberError.textContent = '';
    });
}

if (expiryDateInput) {
    expiryDateInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '').slice(0, 4);
        if (value.length >= 3) {
            value = value.slice(0, 2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });
}

if (cvvInput) {
    cvvInput.addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
    });
}

if (form && cardNumberInput && phoneNumberInput && cardNumberError && phoneNumberError) {
    form.addEventListener('submit', function (e) {
        const cardNumberValue = cardNumberInput.value.replace(/\s/g, '');
        const phoneNumberValue = phoneNumberInput.value;
        let ok = true;
        if (cardNumberValue.length !== 16) {
            e.preventDefault();
            cardNumberError.textContent = 'Card number must be 16 digits';
            ok = false;
        } else {
            cardNumberError.textContent = '';
        }
        if (phoneNumberValue.length !== 10) {
            e.preventDefault();
            phoneNumberError.textContent = 'Phone number must be 10 digits';
            ok = false;
        } else if (ok) {
            phoneNumberError.textContent = '';
        }
    });
}

    </script>

<script src="js/script.js"></script>

<?php include __DIR__ . '/footer.php'; ?>