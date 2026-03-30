<?php
include('connect.php');
    if(isset($_POST['make']) || isset($_POST['shedule_btn'])){
        session_start();
        if(!isset($_SESSION['user_id'])){
            header('Location:login.php');
        }
        else{
            header('Location:appointment.php');
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut Appointment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php 

    include('header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'];

// Initialize variables for the form
$a_name = $username;
$a_email = '';
$a_no = '';
$a_date = '';
$a_time = '';
$a_category = '';
$a_type = '';

// Check if rescheduling
if (isset($_GET['id'])) {
    $app_id = $_GET['id'];
    $query = "SELECT * FROM appointments WHERE a_id = '$app_id' AND id = '$user_id'";
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $appointment = mysqli_fetch_assoc($result);
        $a_email = $appointment['a_email'];
        $a_no = $appointment['a_no'];
        $a_date = $appointment['a_date'];
        $a_time = $appointment['a_time'];
        $a_category = $appointment['a_category'];
        $a_type = $appointment['a_type'];
        $a_time = trim((string) $a_time);
        if ($a_time !== '' && !preg_match('/^\d{2}:\d{2}$/', $a_time)) {
            $ts = strtotime($a_time);
            $a_time = $ts ? date('H:i', $ts) : '';
        }
    } else {
        header('Location: appointment.php'); // Redirect if not found
        exit();
    }
}

// Handle form submission
if (isset($_POST['a-btn'])) {
    if (!empty($_POST['reschedule_id'])) {
        $app_id = $_POST['reschedule_id'];
    }
    $a_email = $_POST['a_email'];
    $a_no = $_POST['a_no'];
    $a_date = $_POST['a_date'];
    $a_time = $_POST['a_time'];
    $time12 = date("g:i A", strtotime($a_time));
    $a_category = $_POST['a_category'];
    $a_type = $_POST['a_type'];

    // Validate inputs
    if (empty($a_email) || empty($a_no) || empty($a_date) || empty($a_time) || empty($a_category) || empty($a_type)) {
        $confirm[] = "Please Fill Out All Details..!!";
    } else {
        if (isset($app_id)) {
            // Update existing appointment
            $update_query = "UPDATE appointments SET a_email = '$a_email', a_no = '$a_no', a_date = '$a_date', a_time = '$time12', a_category = '$a_category', a_type = '$a_type' WHERE a_id = '$app_id'";
            $update_result = mysqli_query($con, $update_query);
            
            // Update appointment history if needed
            $update_history_query = "UPDATE appointment_history SET ah_email = '$a_email', ah_no = '$a_no', ah_date = '$a_date', ah_time = '$time12', ah_category = '$a_category', ah_type = '$a_type' WHERE a_id = '$app_id'";
            mysqli_query($con, $update_history_query);

            if ($update_result) {
                header('Location: thankyou_appointment.php');
                exit();
            } else {
                $confirm[] = 'Could Not Update The Appointment..!';
            }
        } else {
            // Insert new appointment
            $insert = mysqli_query($con, "INSERT INTO appointments (a_name, a_email, a_no, a_date, a_time, a_category, a_type, a_status, id) VALUES ('$a_name', '$a_email', '$a_no', '$a_date', '$time12', '$a_category', '$a_type', 'Pending', '$user_id')");
            
            // Insert into appointment history
            $app_id = mysqli_insert_id($con);
            mysqli_query($con, "INSERT INTO appointment_history (a_id, ah_name, ah_email, ah_no, ah_date, ah_time, ah_category, ah_type, ah_status, id) VALUES ('$app_id', '$a_name', '$a_email', '$a_no', '$a_date', '$time12', '$a_category', '$a_type', 'Pending', '$user_id')");

            if ($insert) {
                header('Location: thankyou_appointment.php');
                exit();
            } else {
                $confirm[] = 'Could Not Add The Appointment..!';
            }
        }
    }
}

    ?>
        <div class="defualt-section">
        <img src="photos/about-img1.jpeg" alt="" class="img">
        <div class="img-content">
            <h2>Book An Appointment</h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <span>Book An Appointment</span>
            </div>
        </div>
    </div>

    <main class="booking-main">
        <div class="booking-shell">
            <div class="booking-grid">
                <aside class="booking-aside" aria-label="Booking benefits">
                    <p class="booking-eyebrow"><i class="fas fa-calendar-check" aria-hidden="true"></i> Reserve your chair</p>
                    <h2 class="booking-aside__title">Premium grooming, <span>on your time</span></h2>
                    <p class="booking-aside__lead">Choose your service and slot in minutes. Walk in relaxed&mdash;we will be ready.</p>
                    <ul class="booking-perks">
                        <li>
                            <span class="booking-perks__icon"><i class="fas fa-scissors" aria-hidden="true"></i></span>
                            <span class="booking-perks__text"><strong>Expert stylists</strong> &mdash; sharp cuts, fades, and finishes.</span>
                        </li>
                        <li>
                            <span class="booking-perks__icon"><i class="fas fa-spray-can-sparkles" aria-hidden="true"></i></span>
                            <span class="booking-perks__text"><strong>Quality products</strong> &mdash; professional care for hair and skin.</span>
                        </li>
                        <li>
                            <span class="booking-perks__icon"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                            <span class="booking-perks__text"><strong>Quick confirmation</strong> &mdash; your request goes straight to our team.</span>
                        </li>
                    </ul>
                    <div class="booking-hours-chip">
                        <i class="far fa-clock" aria-hidden="true"></i>
                        <div>
                            <span class="booking-hours-chip__label">Opening hours</span>
                            <span class="booking-hours-chip__time">Sun &ndash; Sat &middot; 9:00 AM &ndash; 6:00 PM</span>
                        </div>
                    </div>
                </aside>

                <div class="booking-card">
                    <header class="booking-card__head">
                        <h1 class="booking-card__title"><?php echo isset($app_id) ? 'Reschedule Appointment' : 'Book An Appointment'; ?></h1>
                        <p class="booking-card__sub"><?php echo isset($app_id) ? 'Update your visit details below. We will adjust your booking.' : 'Tell us how to reach you and what you need&mdash;we will handle the rest.'; ?></p>
                    </header>
                    <?php
                    if (isset($confirm)) {
                        foreach ($confirm as $message) {
                            echo '<div class="confirm booking-alert" role="alert">' . htmlspecialchars($message) . '</div>';
                        }
                    }
                    ?>
                    <form id="appointmentForm" class="booking-form" method="post" enctype="multipart/form-data">
                        <?php if (isset($app_id)): ?>
                        <input type="hidden" name="reschedule_id" value="<?php echo htmlspecialchars((string) $app_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <div class="booking-field">
                            <label class="booking-field__label" for="email"><i class="far fa-envelope" aria-hidden="true"></i> Email</label>
                            <input class="booking-field__input" type="email" id="email" name="a_email" value="<?php echo htmlspecialchars($a_email); ?>" placeholder="you@example.com" required autocomplete="email">
                        </div>

                        <div class="booking-field">
                            <label class="booking-field__label" for="phone"><i class="fas fa-phone" aria-hidden="true"></i> Phone number</label>
                            <input class="booking-field__input" type="tel" id="phone" name="a_no" value="<?php echo htmlspecialchars($a_no); ?>" placeholder="+91 98765 43210" required autocomplete="tel">
                        </div>

                        <div class="booking-field-row">
                            <div class="booking-field">
                                <label class="booking-field__label" for="date"><i class="far fa-calendar" aria-hidden="true"></i> Date</label>
                                <input class="booking-field__input" type="date" id="date" name="a_date" value="<?php echo htmlspecialchars($a_date); ?>" required>
                            </div>
                            <div class="booking-field">
                                <label class="booking-field__label" for="time"><i class="far fa-clock" aria-hidden="true"></i> Time</label>
                                <input class="booking-field__input" type="time" id="time" name="a_time" value="<?php echo htmlspecialchars($a_time); ?>" required>
                            </div>
                        </div>

                        <div class="booking-field">
                            <label class="booking-field__label" for="service-category"><i class="fas fa-layer-group" aria-hidden="true"></i> Service category</label>
                            <select class="booking-field__select" id="service-category" name="a_category" required>
                                <option value="">Select a category</option>
                                <option value="hair" <?php echo ($a_category == 'hair') ? 'selected' : ''; ?>>Hair cut</option>
                                <option value="beard" <?php echo ($a_category == 'beard') ? 'selected' : ''; ?>>Beard trim</option>
                                <option value="skin" <?php echo ($a_category == 'skin') ? 'selected' : ''; ?>>Skin treatment</option>
                                <option value="spa" <?php echo ($a_category == 'spa') ? 'selected' : ''; ?>>Spa services</option>
                            </select>
                        </div>

                        <div class="booking-field">
                            <label class="booking-field__label" for="service-type"><i class="fas fa-list-ul" aria-hidden="true"></i> Service type</label>
                            <select class="booking-field__select" id="service-type" name="a_type" required data-preselect="<?php echo htmlspecialchars($a_type, ENT_QUOTES, 'UTF-8'); ?>">
                                <option value="">Select a service type</option>
                            </select>
                        </div>

                        <div class="booking-form-actions">
                            <button type="submit" name="a-btn" value="1" class="main-btn">
                                <span><?php echo isset($app_id) ? 'Save new time' : 'Confirm booking'; ?></span>
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>


    
      <!-- shedule -->

      <div class="shedule-container">
        <div class="shedule-inner">
            <div class="shedule-panel shedule-time">
                <div class="shedule-intro">
                    <p class="shedule-eyebrow">Plan your visit</p>
                    <h1>Opening hours</h1>
                    <p class="shedule-tagline">ClassyCut &middot; Hair styles for men</p>
                </div>
                <div class="shedule-hours">
                    <span class="shedule-days">Sunday to Saturday</span>
                    <span class="shedule-hours-accent" aria-hidden="true"></span>
                    <span class="shedule-slot time">9:00 AM &ndash; 6:00 PM</span>
                </div>
                <div class="shedule-contact">
                    <p class="shedule-cta-label">Appointments &amp; enquiries</p>
                    <ul class="shedule-phones">
                        <li><a href="tel:+917575852866">+91 75758 52866</a></li>
                        <li><a href="tel:+919724564257">+91 97245 64257</a></li>
                        <li><a href="tel:+919067669524">+91 90676 69524</a></li>
                    </ul>
                </div>
                <form action="" method="post">
                    <div class="shedule-btn">
                        <button type="submit" name="shedule_btn">Make an appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <script src="js/appoinment.js"></script>
</body>
</html>
