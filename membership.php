<?php
    include('connect.php');
    session_start();

    // royal
$royal="SELECT * FROM royal_membership";
$royal_data= $con->query($royal);

$royal_yearlyPlans = [];
$royal_monthlyPlans = [];

$royal_latestYearlyPrice = 0;
$royal_latestMonthlyPrice = 0;

while ($row = mysqli_fetch_assoc($royal_data)) {
    if ($row["royal_plan"] == 'yearly') {
        $royal_yearlyPlans[] = $row["royal_desc"];

        if ($row["royal_price"] > $royal_latestYearlyPrice) {
            $royal_latestYearlyPrice = $row["royal_price"];
        }
    } elseif ($row["royal_plan"] == 'monthly') {
        $royal_monthlyPlans[] = $row["royal_desc"];

        if ($row["royal_price"] > $royal_latestMonthlyPrice) {
            $royal_latestMonthlyPrice = $row["royal_price"];
        }
    }
}


    // classic
    $classic="SELECT * FROM classic_membership";
    $classic_data= $con->query($classic);
    
    $classic_yearlyPlans = [];
    $classic_monthlyPlans = [];
    
    $classic_latestYearlyPrice = 0;
    $classic_latestMonthlyPrice = 0;
    
    while ($row = mysqli_fetch_assoc($classic_data)) {
        if ($row["classic_plan"] == 'yearly') {
            $classic_yearlyPlans[] = $row["classic_desc"];
    
            if ($row["classic_price"] > $classic_latestYearlyPrice) {
                $classic_latestYearlyPrice = $row["classic_price"];
            }
        } elseif ($row["classic_plan"] == 'monthly') {
            $classic_monthlyPlans[] = $row["classic_desc"];
    
            if ($row["classic_price"] > $classic_latestMonthlyPrice) {
                $classic_latestMonthlyPrice = $row["classic_price"];
            }
        }
    }

    
    
    // standard
$standard="SELECT * FROM standard_membership";
$standard_data= $con->query($standard);

$standard_yearlyPlans = [];
$standard_monthlyPlans = [];

$standard_latestYearlyPrice = 0;
$standard_latestMonthlyPrice = 0;

while ($row = mysqli_fetch_assoc($standard_data)) {
    if ($row["standard_plan"] == 'yearly') {
        $standard_yearlyPlans[] = $row["standard_desc"];

        if ($row["standard_price"] > $standard_latestYearlyPrice) {
            $standard_latestYearlyPrice = $row["standard_price"];
        }
    } elseif ($row["standard_plan"] == 'monthly') {
        $standard_monthlyPlans[] = $row["standard_desc"];

        if ($row["standard_price"] > $standard_latestMonthlyPrice) {
            $standard_latestMonthlyPrice = $row["standard_price"];
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user_id) {
        header("Location: login.php");
        exit();
    }
    $membership_type = $_POST['membership_type']; 
    $price = $_POST['price']; 
    $card_name = $_POST['card-name'];
    $phone_number = $_POST['phone-number'];
    $payment_date = date('Y-m-d H:i:s');

    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO membership_payments (id, membership_type, price, card_name, phone_number, payment_date) 
              VALUES ('$user_id', '$membership_type', '$price', '$card_name', '$phone_number', '$payment_date')";

    if (mysqli_query($con, $query)) {
        header("Location:thankyou_membership.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }
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

        <a href="#" class="logo">
            <img src="photos/logoo.png" alt="">
        </a>
        <nav class="menu">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="service.php">Services</a>
            <a href="eshop.php">E-shop</a>
            <a href="membership.php">Membership</a>
            <a href="contact.php">Contact</a>
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
            <div clsas="menu">
                <a href="index.php">HOME</a> / <span>Our Membership Page</span>
            </div>
           
        </div>
        
     </div>


    <!-- defualt section -->

    <!-- membership section -->

    <div class="membership-container">
        <h1>Our Loyalty Plans</h1>
        <div class="toggle-buttons">
            <button id="yearly-btn" class="active">Yearly</button>
            <button id="monthly-btn">Monthly</button>
        </div>
        <div id="subscriptions">
            <div id="yearly-cards" class="cards">
                <div class="membership-card">
                    <h2>Yearly Royal Pass</h2>
                    
                    <div class="card-content">
                        <ul>
                        <?php foreach ($royal_yearlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                            <!-- <li>50% off On Spa services</li>
                            <li>Unlimited Hair Styling 2 Times a Month</li>
                            <li>Unlimited Beards & Skin Services</li>
                            <li>2 complimentary Hair Style per 3 month</li>
                            <li>2 complimentary Child HairCut Per 3 Month</li>
                            <li>Priority booking With Top stylists</li>
                            <li>Free Product Gift & Samples</li> -->
                        </ul>
                    </div>
                   <?php if ($royal_latestYearlyPrice > 0): ?>
                    <p>₹ <?php echo $royal_latestYearlyPrice; ?>/year</p>
                <?php endif; ?>
                    <!-- <p>₹ 11999/year</p> -->
                    <button>Subscribe</button>
                </div>
                <div class="membership-card">
                    <h2>Yearly Classic Pass</h2>
                    <div class="card-content">
                        <ul>
                            <!-- <li>30% off On Spa services</li>
                            <li>Unlimited Beards & Skin Services</li>
                            <li>1 complimentary Hair Style per month</li>
                            <li>1 complimentary Child HairCut Per Month</li>
                            <li>Priority booking Preferred Stylists</li>
                            <li>Free Product Samples</li> -->
                            <?php foreach ($classic_yearlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ($classic_latestYearlyPrice > 0): ?>
                    <p>₹ <?php echo $classic_latestYearlyPrice; ?>/year</p>
                <?php endif; ?>
                    <!-- <p class="classic">₹ 7999/year</p> -->
                    <button>Subscribe</button>
                </div>
                <div class="membership-card">
                    <h2>Yearly Standard Pass</h2>
                    <div class="card-content">
                        <ul>
                            <!-- <li>15% off On Spa services</li>
                            <li>10% off On Hair Styling</li>
                            <li>5% off On Beard services</li>
                            <li>1 complimentary HairCut Per 3 Months</li>
                            <li>Priority booking</li> -->
                            <?php foreach ($standard_yearlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ($standard_latestYearlyPrice > 0): ?>
                    <p>₹ <?php echo $standard_latestYearlyPrice; ?>/year</p>
                <?php endif; ?>
                    <!-- <p class="y-standard">₹ 3999/year</p> -->
                    <button>Subscribe</button>
                </div>
            </div>
            <div id="monthly-cards" class="cards hidden">
                <div class="membership-card">
                    <h2>Monthly Royal Pass</h2>
                    <div class="card-content">
                        <ul>

                        <?php foreach ($royal_monthlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                            <!-- <li>50% off On Spa services</li>  
                            <li>2 complimentary Hair Style per month</li>
                            <li>2 complimentary Child HairCut Per Month</li>
                            <li>Priority booking With Top stylists</li>
                            <li>Free Product Gift & Samples</li> -->
                        </ul>
                    </div>
                    <?php if ($royal_latestMonthlyPrice > 0): ?>
                    <p>₹ <?php echo $royal_latestMonthlyPrice; ?>/month</p>
                <?php endif; ?>
                    <!-- <p>₹ 1299/month</p> -->
                    <button>Subscribe</button>
                </div>
                <div class="membership-card">
                    <h2>Monthly Classic Pass</h2>
                    <div class="card-content">
                        <ul>
                            <!-- <li>30% off On Spa services</li>
                            <li>1 complimentary Hair Style per month</li>
                            <li>1 complimentary Child HairCut Per Month</li>
                            <li>Priority booking Preferred Stylists</li>
                            <li>Free Product Samples</li> -->
                            <?php foreach ($classic_monthlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <!-- <p>₹ 699/month</p> -->
                    <?php if ($classic_latestMonthlyPrice > 0): ?>
                    <p>₹ <?php echo $classic_latestMonthlyPrice; ?>/month</p>
                <?php endif; ?>
                    <button>Subscribe</button>
                </div>
                <div class="membership-card">
                    <h2>Monthly Standard Pass</h2>
                    <div class="card-content">
                        <ul>
                            <!-- <li>20% off On Spa services</li>
                            <li>10% off On Hair Styling</li>
                            <li>5% off On Beard services</li>
                            <li>Priority booking</li> -->
                            <?php foreach ($standard_monthlyPlans as $desc): ?>
                                <li><?php echo $desc; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ($standard_latestMonthlyPrice > 0): ?>
                    <p>₹ <?php echo $standard_latestMonthlyPrice; ?>/month</p>
                <?php endif; ?>
                    <!-- <p class="m-standard">₹ 399/month</p> -->
                    <button>Subscribe</button>
                </div>
            </div>
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



    <!-- footer sections -->

    <footer>
        <div class="container">
                <div class="row">
                    <div class="f-section" id="company">
                        <h2 class="f-logo">ClassyCut</h2>
                        <p class="f-text">we believe that style is an experience the perfect blend of luxury and professional expertise at ClassyCut.whether you're here for a haircut, a relaxing skin treatment, or a complete makeover</p>
                        <div class="media">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>


                    <div class="f-section" id="links">
                        <h3>Links</h3>
                            <div class="f-menu">
                                <a href="about.php">About</a>
                                <a href="service.php">Services</a>
                                <a href="eshop.php">E - shop</a>
                                <a href="membership.php">Membership</a>
                            </div>
                    </div>


                    <div class="f-section" id="service">
                        <h3>Services</h3>
                        <div class="f-menu">
                            <a href="service.php">Stylish Hair Cut</a>
                            <a href="service.php">Hair Color</a>
                            <a href="service.php">Stylish Beard Trim</a>
                            <a href="service.php">Beard Trim</a>
                            <a href="service.php">Skin Treatment</a>
                            <a href="service.php">Spa Services</a>
                        </div>
                    </div>


                    <div class="f-section" id="contact">
                        <h3>Contact</h3>
                            <div class="detail">
                                <i class="fa fa-phone"></i>
                                <p>Phone: (+91) 7575852866</p>
                            </div>
                            <div class="detail">
                                <i class="fas fa-envelope"></i>
                                <p>classycut007@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; Copyrights @Classycut. All Rights Reserved</p>
                <p>Designed by Ak-Developer</p>
            </div>
    </footer>


    <!-- footer sections -->

    <script>


        // Script for opening and closing the popup
const paymentPopup = document.getElementById('payment-popup');
const closeBtn = document.querySelector('.close-btn');
const membershipNameElement = document.getElementById('membership-name');
const membershipPriceElement = document.getElementById('membership-price');

// Function to show popup with pass name and price
function openPopup(passName, price) {
    membershipNameElement.textContent = passName;
    membershipPriceElement.textContent = price;
    paymentPopup.classList.remove('hidden');
}

// Close the popup when clicking outside of it
window.addEventListener('click', function (event) {
    if (event.target === paymentPopup) {
        paymentPopup.classList.add('hidden');
    }
});


// Close the popup when 'x' is clicked
closeBtn.addEventListener('click', () => {
    paymentPopup.classList.add('hidden');
});

// Add event listeners to each subscribe button
document.querySelectorAll('.membership-card button').forEach(button => {
    button.addEventListener('click', function() {
        const card = this.closest('.membership-card');
        const passName = card.querySelector('h2').textContent;
        const price = card.querySelector('p').textContent.match(/₹\s?([\d,]+)/)[1];
        openPopup(passName, price);
    });
});
// Get form elements
const form = document.getElementById('payment-form');
const cardNumberInput = document.getElementById('card-number');
const cardNumberError = document.getElementById('card-number-error');

// Format the card number into groups of 4 digits
cardNumberInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 16); // Remove non-digits and limit to 16 digits
    value = value.replace(/(.{4})/g, '$1 ').trim(); // Add a space every 4 digits
    e.target.value = value;
    cardNumberError.textContent = ''; // Clear error message when user types
});

// Custom form validation on submit
form.addEventListener('submit', function (e) {
    const cardNumberValue = cardNumberInput.value.replace(/\s/g, ''); // Remove spaces
    if (cardNumberValue.length !== 16) {
        e.preventDefault(); // Prevent form submission
        cardNumberError.textContent = 'Card number must be 16 digits'; // Custom error message
    } else {
        cardNumberError.textContent = ''; // Clear error if valid
    }
});

// Format the expiry date as MM/YY
const expiryDateInput = document.getElementById('expiry-date');
expiryDateInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 4); // Remove non-digits and limit to 4 digits
    if (value.length >= 3) {
        value = value.slice(0, 2) + '/' + value.slice(2); // Insert the slash after the month
    }
    e.target.value = value;
});

// Limit the CVV to 3 digits
const cvvInput = document.getElementById('cvv');
cvvInput.addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3); // Remove non-digits and limit to 3 digits
});

// Phone number validation
const phoneNumberInput = document.getElementById('phone-number');
const phoneNumberError = document.getElementById('phone-number-error');

// Allow only numbers in the phone number field
phoneNumberInput.addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10); // Limit to 10 digits
    phoneNumberError.textContent = ''; // Clear error message when user types
});

// Custom validation for phone number
form.addEventListener('submit', function (e) {
    const phoneNumberValue = phoneNumberInput.value;
    if (phoneNumberValue.length !== 10) {
        e.preventDefault(); // Prevent form submission
        phoneNumberError.textContent = 'Phone number must be 10 digits'; // Custom error message
    } else {
        phoneNumberError.textContent = ''; // Clear error if valid
    }
});

// Function to show popup with pass name and price
// function openPopup(passName, price) {
//     membershipNameElement.textContent = passName;
//     membershipPriceElement.textContent = price;

//     const paymentButton = document.getElementById('payment-btn');
//     paymentButton.textContent = `Pay ₹ ${price}`; // Set button text to price

//     paymentPopup.classList.remove('hidden');
// }


function openPopup(passName, price) {
    membershipNameElement.textContent = passName;
    membershipPriceElement.textContent = price;

    // Set hidden input values
    document.getElementById('membership-type').value = passName; // Set membership type
    document.getElementById('membership-price-hidden').value = price; // Set price

    const paymentButton = document.getElementById('payment-btn');
    paymentButton.textContent = `Pay ₹ ${price}`; // Set button text to price

    paymentPopup.classList.remove('hidden');
}


    </script>

<script src="js/script.js"></script>

</body>

</html>