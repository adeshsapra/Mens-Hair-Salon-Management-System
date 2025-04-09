<?php

include('connect.php');

if(isset($_POST['shedule_btn'])){
        session_start();
        if(!isset($_SESSION['user_id'])){
            header('Location:login.php');
        }
        else{
            header('Location:appointment.php');
        }
}
// hair cut

$haircut="SELECT * FROM haircut_service";
$haircut_data= $con->query($haircut);

$haircut_design = [];
$haircut_style = [];

while ($row = mysqli_fetch_assoc($haircut_data)) {
    if ($row["hair_category"] == 'hairdesign') {
        $haircut_design[] = [
            'service' => $row["hair_service"],
            'price' => $row["hair_price"]
        ];

    } elseif ($row["hair_category"] == 'hairstyle') {
        $haircut_style[] = [
            'service' => $row["hair_service"],
            'price' => $row["hair_price"]
        ];
    }
}

// beard service

$beard="SELECT * FROM beard_service";
$beard_data= $con->query($beard);



// beard service

$skin="SELECT * FROM skin_service";
$skin_data= $con->query($skin);


// spa service

$spa="SELECT * FROM spa_service";
$spa_data= $con->query($spa);

$body_treatment = [];
$body_massage = [];

while ($row = mysqli_fetch_assoc($spa_data)) {
    if ($row["spa_category"] == 'bodytreatment') {
        $body_treatment[] = [
            'service' => $row["spa_service"],
            'price' => $row["spa_price"]
        ];

    } elseif ($row["spa_category"] == 'bodymassage') {
        $body_massage[] = [
            'service' => $row["spa_service"],
            'price' => $row["spa_price"]
        ];
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut services</title>

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
            <h2>Men Salon Services</h2>
            <div clsas="menu">
                <a href="index.php">HOME</a> / <span><a href="appointment.php"> Book An Appointment</a></span>
            </div>
           
        </div>
        
     </div>


    <!-- default section -->

    <!-- service section -->

                <!-- hair services container -->

    <div class="service-container1">
        <div class="service-hair">
            <div class="service-hair-img">
                <img src="photos/services-hair.jpg" alt="">
            </div>
            <div class="content">
            <h1>Hair Cut</h1>
            <p>Experience precision and style with our expert haircut service. Whether you're looking for a dramatic change or a subtle trim, our skilled stylists are here to deliver a look that suits your personality and enhances your features.</p>
            </div>
        </div>
        <div class="price-list">
            <h2>Hair Design</h2>
            <ul>
                <!-- <li>
                    <span>Hair Crop with Wash</span>
                    <span>₹ 350</span>
                </li>
                <li>
                    <span>Hair Color</span>
                    <span>₹ 500</span>
                </li>
                <li>
                    <span>Hair Crop Prince (Up to 10 Yrs)</span>
                    <span>₹ 250</span>
                </li>
                <li>
                    <span>Smooth Hair Shower</span>
                    <span>₹ 150</span>
                </li>
                <li>
                    <span>Dandruff Control</span>
                    <span>₹ 550</span>
                </li> -->

                <?php foreach ($haircut_design as $design): ?>
                <li>
                    <span><?php echo $design['service']; ?></span>
                    <span>₹ <?php echo $design['price']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <h2>Hair Styles</h2>
            <ul>
                <!-- <li>
                    <span>Buzz Cut</span>
                    <span>₹ 250</span>
                </li>
                <li>
                    <span>Fade Cut</span>
                    <span>₹ 300</span>
                </li>
                <li>
                    <span>French Cut</span>
                    <span>₹ 350</span>
                </li>
                <li>
                    <span>Crew Cut</span>
                    <span>₹ 200</span>
                </li>
                <li>
                    <span>Mohawak Cut</span>
                    <span>₹ 500</span>
                </li>
                <li>
                    <span>Caesar Cut</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Textured Cut</span>
                    <span>₹ 600</span>
                </li> -->

                <?php foreach ($haircut_style as $style): ?>
                <li>
                    <span><?php echo $style['service']; ?></span>
                    <span>₹ <?php echo $style['price']; ?></span>
                </li>
                <?php endforeach; ?>


            </ul>
        </div>
    </div>

                    <!-- braed services container -->

    <div class="service-container2">
        <div class="service-beard">
            <div class="content">
                <h1>Beard Trim</h1>
                <p>Refine your look with our professional beard trim service. Our barbers specialize in shaping and grooming facial hair to complement your facial structure and personal style. we'll help you achieve a polished and confident look.</p>
            </div>
            <div class="service-beard-img">
                <img src="photos/services-beard.jpg" alt="">
            </div>
        </div>
        <div class="price-list">
            <h2>Beard Styles</h2>
            <ul>
                <!-- <li>
                    <span>Thick Stubble Beard</span>
                    <span>₹ 200</span>
                </li>
                <li>
                    <span>Long Beard</span>
                    <span>₹ 300</span>
                </li>
                <li>
                    <span>Short Beard</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Trimmed Beard</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Fade Beard</span>
                    <span>₹ 100</span>
                </li>
                <li>
                    <span>Anchor Beard</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Thick Bushy Beard</span>
                    <span>₹ 600</span>
                </li>
                <li>
                    <span>Gotee Beard</span>
                    <span>₹ 350</span>
                </li> -->
                <?php 
                    while($row = mysqli_fetch_assoc($beard_data)){
                ?>
                <li>
                    <span><?php echo $row['beard_service'];?></span>
                    <span>₹ <?php echo $row['beard_price'];?></span>
                </li>
                <?php 
                    }
                ?>
            </ul>
        </div>
    </div>


    <!-- skin services container -->

    <div class="service-container3">
        <div class="service-skin">
            <div class="service-skin-img">
                <img src="photos/facial1.jpg" alt="">
            </div>
            <div class="content">
            <h1>Skin Treatment</h1>
            <p>glowing, healthy skin with our specialized treatments at Classycut. our expert estheticians customize every service to meet your skincare needs.Book your appointment today for a luxurious Experience.</p>
            </div>
        </div>
        <div class="price-list">
            <h2>Skin Care</h2>
            <ul>
                <!-- <li>
                    <span>Men's Facial</span>
                    <span>₹ 150</span>
                </li>
                <li>
                    <span>Brightening Facial</span>
                    <span>₹ 350</span>
                </li>
                <li>
                    <span>HydraFacial</span>
                    <span>₹ 250</span>
                </li>
                <li>
                    <span>Collagen Facial</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Chemical Peel</span>
                    <span>₹ 300</span>
                </li>
                <li>
                    <span>Charcoal Facial</span>
                    <span>₹ 500</span>
                </li>
                <li>
                    <span>Deep Cleansing Facial</span>
                    <span>₹ 500</span>
                </li>
                <li>
                    <span>Oxygen Facial</span>
                    <span>₹ 600</span>
                </li>
                <li>
                    <span>Laser Skin Resurfacing</span>
                    <span>₹ 1000</span>
                </li> -->
                <?php 
                    while($row = mysqli_fetch_assoc($skin_data)){
                ?>
                <li>
                    <span><?php echo $row['skin_service'];?></span>
                    <span>₹ <?php echo $row['skin_price'];?></span>
                </li>
                <?php 
                    }
                ?>
            </ul>
        </div>
    </div>

                    <!-- body services container -->

    <div class="service-container4">
        <div class="service-body">
            <div class="content">
                <h1>Spa Services</h1>
                <p>Experience the ultimate relaxation body treatment services.Our expert therapists combine advanced techniques with premium products to cleanse, exfoliate, and hydrate your skin, leaving it smooth, soft, and glowing.</p>
            </div>
            <div class="service-body-img">
                <img src="photos/spa.jpg" alt="">
            </div>
        </div>
        <div class="price-list">
            <h2>Body Treatment</h2>
            <ul>
                <!-- <li>
                    <span>Body Scrub</span>
                    <span>₹ 400</span>
                </li>
                <li>
                    <span>Hydrating Body Treatment</span>
                    <span>₹ 600</span>
                </li>
                <li>
                    <span>Detoxifying Mud Wrap </span>
                    <span>₹ 700</span>
                </li>
                <li>
                    <span>Cellulite Treatment</span>
                    <span>₹ 350</span>
                </li>
                <li>
                    <span>Paraffin Body Treatment</span>
                    <span>₹ 850</span>
                </li> -->

                <?php foreach ($body_treatment as $treatment): ?>
                <li>
                    <span><?php echo $treatment['service']; ?></span>
                    <span>₹ <?php echo $treatment['price']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul> 
            <h2>Body Massage</h2>
            <ul>
                <!-- <li>
                    <span>Full Body Exfoliation</span>
                    <span>₹ 2500</span>
                </li>
                <li>
                    <span>Full Hand Massage</span>
                    <span>₹ 1200</span>
                </li>
                <li>
                    <span>Head and Shoulder Massage</span>
                    <span>₹ 800</span>
                </li>
                <li>
                    <span>Massage & Wrap</span>
                    <span>₹ 3500</span>
                </li>
                <li>
                    <span>Hot Stone Massage</span>
                    <span>₹ 3000</span>
                </li>
                <li>
                    <span>Deep Tissue Massage</span>
                    <span>₹ 2000</span>
                </li>
                <li>
                    <span>Ayurvedic Massage</span>
                    <span>₹ 1500</span>
                </li> -->

                <?php foreach ($body_massage as $massage): ?>
                <li>
                    <span><?php echo $massage['service']; ?></span>
                    <span>₹ <?php echo $massage['price']; ?></span>
                </li>
                <?php endforeach; ?>


            </ul>
        </div>
    </div>



    <!-- service section -->

     <!-- our shedule -->
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