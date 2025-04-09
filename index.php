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
    <title>ClassyCut Salon</title>

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
    <!-- ----------------home section-------- -->
    <section class="home">
        <img src="photos/homebest.png" alt="Background Image" class="home-bg">
        <div class="content">
            <h3>Staying In Style Forever</h3>
            <p>
                We make your hair look perfect, our expert stylists create professional works of art and try to exceed your expectations.
            </p>
            <!-- <div> -->
                <form action="" method="post" class="index-form-button">
                <a href="appointment.php"><button name="make" class="main-btn">Make an Appointment</button></a>
                </form>
                <!-- <a href="appointment.php">Make an Appointment</a> -->
            <!-- </div> -->
        </div>
    </section>

        <!-- sevices section -->
    <div class="home-color">
        <h2>our salon service</h2>
        <h6>- gentalemen's comes to the professionals -</h6>
        <div class="main-page-container">
            <div class="service-card">
                <img src="photos/hair.jpg" alt="">
                <h3>hair cut</h3>
                <p>Experience precision and style with our expert haircut service. Whether you're looking for a dramatic change or a subtle trim, our skilled stylists are here to deliver a look that suits your personality and enhances your features.</p>
                <a href="service.php">Read More...</a>
            </div>
            <div class="service-card">
                <img src="photos/beard.jpg" alt="">
                <h3>beard trim</h3>
                <p>Refine your look with our professional beard trim service. Our barbers specialize in shaping and grooming facial hair to complement your facial structure and personal style. we'll help you achieve a polished and confident look.</p>
                <a href="service.php">Read More...</a>
            </div>
            <div class="service-card">
                <img src="photos/skin.jpg" alt="">
                <h3>skin treatment</h3>
                <p>glowing, healthy skin with our specialized treatments at Classycut. our expert estheticians customize every service to meet your skincare needs.Book your appointment today for a luxurious Experience.</p>
                <a href="service.php">Read More...</a>
            </div>
            <div class="service-card">
                <img src="photos/body.jpg" alt="">
                <h3>Spa Services</h3>
                <p>Experience the ultimate relaxation body treatment services.Our expert therapists combine advanced techniques with premium products to cleanse, exfoliate, and hydrate your skin, leaving it smooth, soft, and glowing.</p>
                <a href="service.php">Read More...</a>
            </div>
            <div class="viewall-btn">
                <a href="service.php">View All Services</a>
            </div>
        </div>
    </div>

        <!-- service section -->
    

        <!-- product section -->

    <div class="product-home-main-container">
        <h1>Our E-shop Products</h1>
        <p>- Luxury-crafted and elegant premium quality product -</p>
        <div class="product-home-container">
            <div class="product-home-card">
                <img src="products/haircare.jpg" alt="Product 2">
                <h3>Hair care</h3>
                <p>ClassyCut Provides premium hair care solutions for effortlessly elegant and healthy hair.</p>
            </div>

            <div class="product-home-card">
                <img src="products/homeoil.jpg" alt="Product 2">
                <h3>Beard care</h3>
                <p>ClassyCut enhances your beard care routine with products designed for a refined look.</p>
            </div>

            <div class="product-home-card">
                <img src="products/homesskin.jpg" alt="Product 3">
                <h3>skin care</h3>
                <p>ClassyCut offers luxurious skin care that enhances your natural beauty with every touch.</p>
            </div>

            <div class="product-home-card">
                <img src="products/facemask.jpg" alt="Product 4">
                <h3>Facial Masks</h3>
                <p>ClassyCut Provides high-performance facial masks that refresh your complexion.</p>
            </div>
        </div>
        <div class="view-product-btn">
            <a href="eshop.php">View All Products</a>
        </div>
    </div>

        <!-- product section -->

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

    <!-- ----------------home section-------- -->

    <!-- footer sections -->
     <?php 
          include('footer.php');
     ?>

    <script src="js/script.js"></script>
</body>

</html>