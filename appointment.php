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

    
$username = $_SESSION['username'];
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
    } else {
        header('Location: appointment.php'); // Redirect if not found
        exit();
    }
}

// Handle form submission
if (isset($_POST['a-btn'])) {
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
            <h2>Make Appointment</h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <span>Book An Appointment</span>
            </div>
        </div>
    </div>

    <div class="appointment-container">
        <h1><?php echo isset($app_id) ? 'Reschedule Appointment' : 'Book An Appointment'; ?></h1>
        <?php
        if (isset($confirm)) {
            foreach ($confirm as $message) {
                echo '<div class="confirm">' . $message . '</div>';
            }
        }
        ?>
        <form id="appointmentForm" method="post" enctype="multipart/form-data">
            <label for="email">Email:</label>
            <input type="email" id="email" name="a_email" value="<?php echo htmlspecialchars($a_email); ?>" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="a_no" value="<?php echo htmlspecialchars($a_no); ?>" required>

            <label for="date">Appointment Date:</label>
            <input type="date" id="date" name="a_date" value="<?php echo htmlspecialchars($a_date); ?>" required>

            <label for="time">Appointment Time:</label>
            <input type="time" name="a_time" value="<?php echo htmlspecialchars($a_time); ?>" required>

            <label for="service-category">Service Category:</label>
            <select id="service-category" name="a_category" required>
                <option value="">Select a Service Category</option>
                <option value="hair" <?php echo ($a_category == 'hair') ? 'selected' : ''; ?>>Hair Cut</option>
                <option value="beard" <?php echo ($a_category == 'beard') ? 'selected' : ''; ?>>Beard Trim</option>
                <option value="skin" <?php echo ($a_category == 'skin') ? 'selected' : ''; ?>>Skin Treatment</option>
                <option value="spa" <?php echo ($a_category == 'spa') ? 'selected' : ''; ?>>Spa Services</option>
            </select>

            <label for="service-type">Service Type:</label>
            <select id="service-type" name="a_type" required>
                <option value="">Select a Service Type</option>
                <!-- <option value="Type1" <?php //echo ($a_type == 'Type1') ? 'selected' : ''; ?>>Type 1</option> -->
                <!-- <option value="Type2" <?php//echo ($a_type == 'Type2') ? 'selected' : ''; ?>>Type 2</option> -->
            </select>

            <button type="submit" name="a-btn"><?php echo isset($app_id) ? 'Confirm Appointment' : 'Book Appointment'; ?></button>
        </form>
    </div>


    
      <!-- shedule -->

      <div class="shedule-container">
        <div class="shedule-time">
            <h1>Opening Hours</h1>
            <h6>- ClassyCut Hair Styles For Men -</h6>
            <p>Sunday to Saturday, </p><p class="time">9AM - 6PM</p>
            <p>For Appointment OR Enquiries:</p>
            <p class="phone-numbers">
                <span>+91 75758 52866</span>
                <span>+91 97245 64257</span>
                <span>+91 90676 69524</span>
            </p>
            <form action="" method="post">
                <div class="shedule-btn">
                    <button type="submit" name="shedule_btn">Make an Appointment</button>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <script src="js/appoinment.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
