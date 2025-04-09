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
        <title>ClassyCut About</title>

        <!-- font awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <!-- box link -->
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>

        <!-- header and navigation section -->

        <?php
        include('header.php');
        ?>

        <!-- header and navigation section -->
        
        <!-- defualt section -->  
        <div class="defualt-section">
            <img src="photos/about-img1.jpeg" alt="" class="img">
            <div class="img-content">
                <h2>About Us</h2>
                <div clsas="menu">
                    <a href="index.php">HOME</a> / <span>Our About Page</span>
                </div>
            </div>
        </div>

         <!-- defualt section -->  

        <section class="photo-nav">
            <div class="content">
                <h1>About ClassyCut</h1>
                <p>Welcome to at classycut salon, we're passionate about helping you look and feel your best. Our team of expert stylists and technicians are dedicated to providing exceptional service and unparalleled expertise in a warm and welcoming environment.
                </p>
                <h4>Our Story</h4>
                <p>
                    The Salon Is Founded in 2024 ,  Our mission is to provide a personalized experience for each guest, tailoring our services to meet their unique needs and preferences.

                </p>
                <h4>Why Choose Us?</h4>
                <p>
                    - Expertise: Our team of stylists and technicians are highly trained and experienced in the latest techniques and trends.<br>
                    - Personalized Service: We take the time to understand your unique needs and preferences, tailoring our services to meet your individual style.<br>
                    - High-Quality Products: We use only the best products to ensure exceptional results and long-lasting beauty.<br>
                    - Relaxing Atmosphere: Our salon is designed to provide a peaceful and calming environment, making your visit a true escape.<br>
                    - Convenience: We offer flexible scheduling and online booking to fit your busy lifestyle.
                </p>
            </div>
            <div class="video">
                <video autoplay muted loop>
                    <source src="photos/about.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </section>

        <section class="about-skill">
            <div class="about-skill-img">
                <img src="photos/homenew.jpg" alt="">
            </div>
            <div class="content">
                <h1>Our Professional Skill</h1>
                <p>
                    Our team of grooming professionals has extensive experience in haircuts, shaves, beard grooming, and other grooming services. We use the latest techniques and tools to ensure that our clients receive top-quality services that meet their unique grooming needs.
                </p>
                
                <div class="progress-bar-container">
                    <div class="label">Haircut</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 90%;"></div>
                    </div>
                </div>
            
                <div class="progress-bar-container">
                    <div class="label">Beard Grooming</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 70%;"></div>
                    </div>
                </div>
            
                <div class="progress-bar-container">
                    <div class="label">Skin Care</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 50%;"></div>
                    </div>
                </div>
            
                <h1>Our Advance System</h1>
                <p>
                    our classyCut salon management system allows for a seamless booking and appointments.with our easy to use online booking platform, you can quickly shedule your appointments at your convenience and ensure timely service delivery.
                </p>
            </div>
        </section>

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
    
        <!-- footer sections -->

        <?php 
          include('footer.php');
        ?>


        <!-- footer sections -->

        <script src="js/script.js"></script>
    </body>

    </html>