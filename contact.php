<?php
    include('connect.php');
    if (isset($_POST['con-btn'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $message = $_POST['message'];

        // Create insert query
        $sql = "INSERT INTO contact_details (c_name, c_email, c_phone, c_message) VALUES ('$name', '$email', '$phone', '$message')";
        if ($con->query($sql) === TRUE) {
            echo "<script>alert('Message sent successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $sql . "<br>" . $con->error . "');</script>";
        }
    }

    // Close connection
    $con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut Contact</title>

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- box link -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        /* Pop-up Styles */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 50px 60px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            border-radius: 8px;
            text-align: center;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(5px);
            z-index: 999;
        }

        .popup i {
            margin-bottom: 10px;
            color: green; 
            font-size: 70px;
        }
    </style>
</head>
<body>

    <!-- header and navigation section -->
    <?php include('header.php'); ?>

    <!-- default section -->
    <div class="defualt-section">
        <img src="photos/about-img1.jpeg" alt="" class="img">
        <div class="img-content">
            <h2>Contact Us</h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <span>Our Contact Page</span>
            </div>
        </div>
    </div>

    <!-- contact main container -->
    <div class="contact-main-container">
        <div class="contact-container">
            <div class="contact-form">
                <h2>Contact Us</h2>
                <form action="" method="POST" onsubmit="showPopup(event)">
                    <div class="input-box">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="input-box">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="input-box">
                        <input type="text" name="phone" placeholder="Your Phone" required>
                    </div>
                    <div class="input-box">
                        <textarea name="message" placeholder="Your Message" required></textarea>
                    </div>
                    <div class="input-box">
                        <button type="submit">Send Message</button>
                    </div>
                </form>
                <div class="social-media">
                    <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="contact-details">
                <h3>Contact Details</h3>
                <p>Mahuva Road, Savarkundla, Amreli, Gujarat</p>
                <p>Email: classycut007@gmail.com</p>
                <p>Phone: +91 7575852866</p>
                <div class="map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3709.352952506167!2d71.2230961752118!3d21.611165967520822!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395880d05dcb3a59%3A0x1768ffa86cf05a5!2sKamani%20Science%20College%20And%20Prataprai%20Arts%20College!5e0!3m2!1sen!2sin!4v1723385829225!5m2!1sen!2sin" width="600" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- footer sections -->
    <?php include('footer.php'); ?>

    <script src="js/script.js"></script>

    <!-- Popup section -->
    <div class="popup-overlay" id="popup-overlay"></div>
    <div class="popup" id="popup">
        <i class="fas fa-check-circle"></i>
        <p style="font-size: 18px; margin-top: 10px;">Message sent successfully!</p>
    </div>

    <script>
        function showPopup(event) {
            event.preventDefault(); 
            document.getElementById('popup').style.display = 'block';
            document.getElementById('popup-overlay').style.display = 'block';
            setTimeout(function() {
                document.getElementById('popup').style.display = 'none';
                document.getElementById('popup-overlay').style.display = 'none';
                
                // Submit the form
                event.target.submit();
            }, 2000);
        }
    </script>
</body>
</html>
