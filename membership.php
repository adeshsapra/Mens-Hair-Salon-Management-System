<?php
include('connect.php');
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
require_once __DIR__ . '/stripe_config.php';

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
$stripe_membership_enabled = STRIPE_ENABLED && STRIPE_PUBLISHABLE_KEY !== '' && STRIPE_SECRET_KEY !== '';
$membership_user_logged_in = isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
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
     <script src="https://js.stripe.com/v3/"></script>

<style>
     
     :root {
    --bg1: #18150d; /* Dark background */
    --bg2: #eae3c2; /* Light background */
    --body: #a39623; /* Body color */
    --brand: #cbb90f; /* Brand color */
    --white: #fff; /* White color */
        }

        .membership-checkout-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(10, 10, 10, 0.72);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 16px;
            animation: fadeIn 0.25s ease;
        }

        .membership-checkout-card {
            background: var(--white);
            width: min(930px, 100%);
            border-radius: 16px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
        }

        .membership-checkout-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 0;
        }

        .membership-checkout-left {
            padding: 28px 24px 24px;
        }

        .membership-checkout-right {
            background: #f8f8f4;
            border-left: 1px solid rgba(24, 21, 13, 0.08);
            padding: 28px 24px 24px;
        }

        .membership-checkout-left h2,
        .membership-checkout-right h3 {
            margin: 0 0 12px;
            color: var(--bg1);
            text-transform: none;
        }

        .membership-checkout-subtext {
            margin: 0 0 16px;
            color: #555;
            font-size: 14px;
            text-transform: none;
            line-height: 1.5;
        }

        .membership-form-grid {
            display: grid;
            gap: 12px;
        }

        .membership-form-group label {
            display: block;
            margin-bottom: 6px;
            color: #262626;
            font-size: 13px;
            font-weight: 700;
            text-transform: none;
        }

        .membership-form-group input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d5d5d5;
            border-radius: 10px;
            font-size: 14px;
            text-transform: none;
        }

        .membership-form-group input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(203, 185, 15, 0.16);
        }

        #membership-card-element {
            padding: 12px;
            border: 1px solid #d5d5d5;
            border-radius: 10px;
            background: #fff;
        }

        #membership-card-errors {
            min-height: 20px;
            color: #d93025;
            font-size: 13px;
            margin-top: 6px;
            text-transform: none;
        }

        .membership-pay-btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            background: var(--bg1);
            color: var(--bg2);
            font-size: 15px;
            font-weight: 700;
            padding: 12px 14px;
            margin-top: 10px;
        }

        .membership-pay-btn:hover {
            background: var(--brand);
            color: var(--bg1);
        }

        .membership-pay-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .membership-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            text-transform: none;
        }

        .membership-summary-row strong {
            color: #111;
            text-transform: none;
        }

        .membership-summary-features {
            margin: 12px 0 0;
            padding-left: 18px;
            display: grid;
            gap: 7px;
        }

        .membership-summary-features li {
            color: #444;
            font-size: 13px;
            text-transform: none;
        }

        .membership-modal-close {
            position: absolute;
            top: 8px;
            right: 10px;
            border: none;
            background: transparent;
            font-size: 27px;
            color: #666;
            cursor: pointer;
            line-height: 1;
        }

        .membership-checkout-warning {
            background: #fdecea;
            border: 1px solid #f5c7c2;
            color: #b42318;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: none;
        }

        #membership-toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100001;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .membership-toast {
            min-width: 260px;
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.24);
            transform: translateX(120%);
            transition: transform 0.28s ease;
        }

        .membership-toast.show {
            transform: translateX(0);
        }

        .membership-toast.success {
            background: #108f53;
        }

        .membership-toast.error {
            background: #d93025;
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

        @media (max-width: 860px) {
            .membership-checkout-grid {
                grid-template-columns: 1fr;
            }
            .membership-checkout-right {
                border-left: 0;
                border-top: 1px solid rgba(24, 21, 13, 0.08);
            }
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
                    <button
                        type="button"
                        class="<?php echo htmlspecialchars($cta_class); ?>"
                        data-checkout-name="<?php echo htmlspecialchars($checkout_name); ?>"
                        data-pass-key="<?php echo htmlspecialchars($pk); ?>"
                        data-billing-plan="<?php echo htmlspecialchars($period); ?>"
                        data-display-name="<?php echo htmlspecialchars($slot['display_name']); ?>"
                        data-price="<?php echo (int) $price; ?>"
                        data-features="<?php echo htmlspecialchars(json_encode(array_values($slot['features'])), ENT_QUOTES, 'UTF-8'); ?>"
                    >Subscribe</button>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- membership section -->

    <div id="membership-checkout-modal" class="membership-checkout-modal hidden">
        <div class="membership-checkout-card">
            <button type="button" class="membership-modal-close" id="membership-modal-close">&times;</button>
            <div class="membership-checkout-grid">
                <section class="membership-checkout-left">
                    <h2>Membership Checkout</h2>
                    <p class="membership-checkout-subtext">Enter your details and pay securely with Stripe.</p>

                    <?php if (!$stripe_membership_enabled): ?>
                        <div class="membership-checkout-warning">Stripe is not connected right now. Please contact admin to enable membership card payments.</div>
                    <?php endif; ?>

                    <form id="membership-payment-form" novalidate>
                        <input type="hidden" id="membership-pass-key" name="pass_key">
                        <input type="hidden" id="membership-billing-plan" name="billing_plan">

                        <div class="membership-form-grid">
                            <div class="membership-form-group">
                                <label for="membership-card-name">Name on Card</label>
                                <input type="text" id="membership-card-name" name="card_name" placeholder="Enter card holder name" required>
                            </div>
                            <div class="membership-form-group">
                                <label for="membership-phone-number">Phone Number</label>
                                <input type="tel" id="membership-phone-number" name="phone_number" maxlength="10" placeholder="10 digit mobile number" required>
                            </div>
                            <div class="membership-form-group">
                                <label>Card Details</label>
                                <div id="membership-card-element"></div>
                                <div id="membership-card-errors"></div>
                            </div>
                        </div>

                        <button type="submit" id="membership-pay-btn" class="membership-pay-btn" <?php echo $stripe_membership_enabled ? '' : 'disabled'; ?>>
                            Pay Now
                        </button>
                    </form>
                </section>

                <aside class="membership-checkout-right">
                    <h3>Order Summary</h3>
                    <div class="membership-summary-row">
                        <span>Plan</span>
                        <strong id="membership-summary-name">-</strong>
                    </div>
                    <div class="membership-summary-row">
                        <span>Billing</span>
                        <strong id="membership-summary-billing">-</strong>
                    </div>
                    <div class="membership-summary-row">
                        <span>Total</span>
                        <strong id="membership-summary-price">₹ 0</strong>
                    </div>
                    <ul class="membership-summary-features" id="membership-summary-features"></ul>
                </aside>
            </div>
        </div>
    </div>

    <div id="membership-toast-container"></div>

    <script>
        const membershipModal = document.getElementById('membership-checkout-modal');
        const membershipCloseBtn = document.getElementById('membership-modal-close');
        const membershipForm = document.getElementById('membership-payment-form');
        const membershipPayBtn = document.getElementById('membership-pay-btn');
        const membershipCardNameInput = document.getElementById('membership-card-name');
        const membershipPhoneInput = document.getElementById('membership-phone-number');
        const membershipPassInput = document.getElementById('membership-pass-key');
        const membershipBillingInput = document.getElementById('membership-billing-plan');
        const membershipSummaryName = document.getElementById('membership-summary-name');
        const membershipSummaryBilling = document.getElementById('membership-summary-billing');
        const membershipSummaryPrice = document.getElementById('membership-summary-price');
        const membershipSummaryFeatures = document.getElementById('membership-summary-features');
        const membershipCardErrors = document.getElementById('membership-card-errors');

        const stripeEnabled = <?php echo $stripe_membership_enabled ? 'true' : 'false'; ?>;
        const stripePublishableKey = '<?php echo addslashes(STRIPE_PUBLISHABLE_KEY); ?>';
        const userLoggedIn = <?php echo $membership_user_logged_in ? 'true' : 'false'; ?>;

        let selectedPlan = null;
        let stripe = null;
        let stripeCard = null;

        function showMembershipToast(message, type = 'success') {
            const container = document.getElementById('membership-toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `membership-toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3400);
        }

        <?php if (isset($_SESSION['toast-msg'])): ?>
            showMembershipToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo $_SESSION['toast-type'] ?? 'success'; ?>");
            <?php unset($_SESSION['toast-msg'], $_SESSION['toast-type']); ?>
        <?php endif; ?>

        function formatINR(amount) {
            return new Intl.NumberFormat('en-IN').format(Number(amount || 0));
        }

        function closeMembershipModal() {
            membershipModal.classList.add('hidden');
            if (membershipCardErrors) {
                membershipCardErrors.textContent = '';
            }
        }

        function openMembershipModal(plan) {
            selectedPlan = plan;
            membershipPassInput.value = plan.passKey;
            membershipBillingInput.value = plan.billingPlan;
            membershipSummaryName.textContent = plan.checkoutName;
            membershipSummaryBilling.textContent = plan.billingPlan === 'yearly' ? 'Annual' : 'Monthly';
            membershipSummaryPrice.textContent = `₹ ${formatINR(plan.price)}`;

            membershipSummaryFeatures.innerHTML = '';
            plan.features.forEach((feature) => {
                const li = document.createElement('li');
                li.textContent = feature;
                membershipSummaryFeatures.appendChild(li);
            });

            membershipPayBtn.textContent = `Pay ₹ ${formatINR(plan.price)}`;
            membershipModal.classList.remove('hidden');
        }

        if (stripeEnabled && stripePublishableKey) {
            stripe = Stripe(stripePublishableKey);
            const elements = stripe.elements();
            stripeCard = elements.create('card', {
                style: {
                    base: {
                        color: '#18150d',
                        fontFamily: '"Kanit", sans-serif',
                        fontSize: '15px',
                        '::placeholder': { color: '#8b8b8b' }
                    },
                    invalid: {
                        color: '#d93025'
                    }
                }
            });
            stripeCard.mount('#membership-card-element');
            stripeCard.on('change', function(event) {
                membershipCardErrors.textContent = event.error ? event.error.message : '';
            });
        }

        membershipCloseBtn.addEventListener('click', closeMembershipModal);
        window.addEventListener('click', function(event) {
            if (event.target === membershipModal) {
                closeMembershipModal();
            }
        });

        membershipPhoneInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        });

        document.querySelectorAll('.membership-card-cta').forEach((button) => {
            button.addEventListener('click', function() {
                if (!userLoggedIn) {
                    window.location.href = 'login.php';
                    return;
                }
                if (!stripeEnabled) {
                    showMembershipToast('Stripe checkout is currently unavailable. Please contact admin.', 'error');
                    return;
                }

                const featuresRaw = this.getAttribute('data-features') || '[]';
                let parsedFeatures = [];
                try {
                    parsedFeatures = JSON.parse(featuresRaw);
                } catch (e) {
                    parsedFeatures = [];
                }

                openMembershipModal({
                    passKey: this.getAttribute('data-pass-key'),
                    billingPlan: this.getAttribute('data-billing-plan'),
                    checkoutName: this.getAttribute('data-checkout-name'),
                    price: Number(this.getAttribute('data-price') || 0),
                    features: Array.isArray(parsedFeatures) ? parsedFeatures : [],
                });
            });
        });

        membershipForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!selectedPlan) {
                showMembershipToast('Please select a membership plan first.', 'error');
                return;
            }

            const cardName = membershipCardNameInput.value.trim();
            const phone = membershipPhoneInput.value.trim();

            if (cardName === '') {
                showMembershipToast('Please enter card holder name.', 'error');
                return;
            }
            if (!/^\d{10}$/.test(phone)) {
                showMembershipToast('Phone number must be exactly 10 digits.', 'error');
                return;
            }
            if (!stripe || !stripeCard) {
                showMembershipToast('Stripe is not available right now.', 'error');
                return;
            }

            membershipPayBtn.disabled = true;
            membershipPayBtn.textContent = 'Processing...';

            try {
                const payload = new URLSearchParams();
                payload.set('pass_key', selectedPlan.passKey);
                payload.set('billing_plan', selectedPlan.billingPlan);

                const intentResponse = await fetch('membership_create_payment_intent.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                const intentData = await intentResponse.json();

                if (intentData.error) {
                    showMembershipToast(intentData.error, 'error');
                    membershipPayBtn.disabled = false;
                    membershipPayBtn.textContent = `Pay ₹ ${formatINR(selectedPlan.price)}`;
                    return;
                }

                const { paymentIntent, error } = await stripe.confirmCardPayment(intentData.client_secret, {
                    payment_method: {
                        card: stripeCard,
                        billing_details: { name: cardName }
                    }
                });

                if (error) {
                    membershipCardErrors.textContent = error.message || 'Payment failed.';
                    membershipPayBtn.disabled = false;
                    membershipPayBtn.textContent = `Pay ₹ ${formatINR(selectedPlan.price)}`;
                    return;
                }

                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    const submitForm = document.createElement('form');
                    submitForm.method = 'POST';
                    submitForm.action = 'membership_handle_stripe_payment.php';
                    const fields = {
                        payment_intent_id: paymentIntent.id,
                        pass_key: selectedPlan.passKey,
                        billing_plan: selectedPlan.billingPlan,
                        card_name: cardName,
                        phone_number: phone
                    };
                    Object.keys(fields).forEach((key) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = fields[key];
                        submitForm.appendChild(input);
                    });
                    document.body.appendChild(submitForm);
                    submitForm.submit();
                } else {
                    showMembershipToast('Unable to confirm payment. Please try again.', 'error');
                    membershipPayBtn.disabled = false;
                    membershipPayBtn.textContent = `Pay ₹ ${formatINR(selectedPlan.price)}`;
                }
            } catch (err) {
                console.error(err);
                showMembershipToast('An unexpected error occurred. Please try again.', 'error');
                membershipPayBtn.disabled = false;
                membershipPayBtn.textContent = `Pay ₹ ${formatINR(selectedPlan.price)}`;
            }
        });
    </script>

<script src="js/script.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
