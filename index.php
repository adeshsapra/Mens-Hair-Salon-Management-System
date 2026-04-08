<?php
include('connect.php');

if (isset($_POST['con-btn'])) {
    $name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($con, $_POST['phone'] ?? '');
    $message = mysqli_real_escape_string($con, $_POST['message'] ?? '');
    $sql = "INSERT INTO contact_details (c_name, c_email, c_phone, c_message) VALUES ('$name', '$email', '$phone', '$message')";
    if ($con->query($sql) === TRUE) {
        header('Location: index.php?contact=sent#contact');
        exit;
    }
}

$show_contact_sent = isset($_GET['contact']) && $_GET['contact'] === 'sent';

if (isset($_POST['make']) || isset($_POST['shedule_btn'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location:login.php');
    } else {
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

<body class="home-page">

    <!-- header and navigation section -->

    <?php
    include('header.php');
    ?>
    <!-- ----------------home section-------- -->
    <section class="home">
        <img src="photos/homebest.png" alt="Background Image" class="home-bg">
        <div class="content hero-content">
            <span class="hero-kicker hero-animate" data-hero-delay="0.05s">Premium men&rsquo;s grooming studio</span>
            <h3 class="hero-title" data-hero-split>Staying In Style Forever</h3>
            <p class="hero-animate" data-hero-delay="0.45s">
                We craft sharp haircuts, clean beard lines, and modern grooming looks with detail-focused service that is designed around your style.
            </p>
            <div class="hero-actions hero-animate" data-hero-delay="0.7s">
                <a href="appointment.php" class="main-btn hero-btn-primary">Make An Appointment</a>
                <a href="eshop.php" class="hero-btn-secondary">View E-Shop</a>
            </div>
            <div class="hero-points hero-animate" data-hero-delay="0.95s">
                <span><i class="fa-solid fa-scissors" aria-hidden="true"></i> Expert Stylists</span>
                <span><i class="fa-solid fa-clock" aria-hidden="true"></i> Easy Online Booking</span>
                <span><i class="fa-solid fa-star" aria-hidden="true"></i> Premium Products</span>
            </div>
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

    <div class="home-about-heading" id="about">
        <h2>About ClassyCut</h2>
        <h6>Your trusted destination for premium men&rsquo;s grooming</h6>
    </div>

    <section class="photo-nav">
        <div class="content">
            <h1>About ClassyCut</h1>
            <p>Welcome to ClassyCut salon, we&rsquo;re passionate about helping you look and feel your best. Our team of expert stylists and technicians are dedicated to providing exceptional service and unparalleled expertise in a warm and welcoming environment.</p>
            <h4>Our Story</h4>
            <p>The salon was founded in 2024. Our mission is to provide a personalized experience for each guest, tailoring our services to meet their unique needs and preferences.</p>
            <h4>Why Choose Us?</h4>
            <p>
                <strong>Expertise:</strong> Our team is highly trained in the latest techniques and trends.<br>
                <strong>Personalized service:</strong> We tailor every visit to your style and preferences.<br>
                <strong>Quality products:</strong> We use premium products for lasting results.<br>
                <strong>Relaxing atmosphere:</strong> A calm space so your visit feels like an escape.<br>
                <strong>Convenience:</strong> Flexible scheduling and easy online booking.
            </p>
        </div>
        <div class="video">
            <video autoplay muted loop playsinline>
                <source src="photos/about.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </section>

    <section class="about-skill">
        <div class="about-skill-img">
            <img src="photos/homenew.jpg" alt="ClassyCut salon interior">
        </div>
        <div class="content">
            <h1>Our Professional Skill</h1>
            <p>Our grooming professionals have extensive experience in haircuts, shaves, beard grooming, and more. We use the latest techniques and tools so every client gets top-quality service.</p>
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
            <h1>Our Advanced System</h1>
            <p>ClassyCut&rsquo;s salon management system makes booking simple. Schedule appointments online at your convenience and enjoy timely, professional service.</p>
        </div>
    </section>

    <div class="contact-main-container" id="contact">
        <div class="contact-container">
            <div class="contact-form">
                <h2>Contact Us</h2>
                <form action="index.php" method="post">
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
                        <button type="submit" name="con-btn" value="1">Send Message</button>
                    </div>
                </form>
                <div class="social-media">
                    <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="contact-details">
                <h3>Contact Details</h3>
                <p>Mahuva Road, Savarkundla, Amreli, Gujarat</p>
                <p>Email: classycut007@gmail.com</p>
                <p>Phone: +91 7575852866</p>
                <div class="map">
                    <iframe title="ClassyCut location map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3709.352952506167!2d71.2230961752118!3d21.611165967520822!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395880d05dcb3a59%3A0x1768ffa86cf05a5!2sKamani%20Science%20College%20And%20Prataprai%20Arts%20College!5e0!3m2!1sen!2sin!4v1723385829225!5m2!1sen!2sin" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
    <?php if ($show_contact_sent): ?>
        <div class="contact-success-popup-overlay" id="contactSuccessPopup" role="dialog" aria-modal="true" aria-labelledby="contactSuccessTitle">
            <div class="contact-success-popup" role="document">
                <button type="button" class="contact-success-popup__close" id="contactSuccessClose" aria-label="Close success message">&times;</button>
                <div class="contact-success-popup__icon" aria-hidden="true"><i class="fas fa-circle-check"></i></div>
                <div class="contact-success-popup__chip">ClassyCut Support</div>
                <h3 id="contactSuccessTitle">Message Sent Successfully</h3>
                <p>Thank you for contacting us. Our team will review your message and get back to you shortly.</p>
                <div class="contact-success-popup__meta">Auto closing in 5 seconds</div>
                <div class="contact-success-popup__progress" aria-hidden="true"><span></span></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var popup = document.getElementById('contactSuccessPopup');
                var closeBtn = document.getElementById('contactSuccessClose');
                if (!popup) return;

                popup.classList.add('show');

                function cleanContactSuccessQuery() {
                    try {
                        var url = new URL(window.location.href);
                        if (url.searchParams.get('contact') === 'sent') {
                            url.searchParams.delete('contact');
                            var cleanUrl = url.pathname + (url.search ? url.search : '') + (url.hash || '#contact');
                            window.history.replaceState({}, '', cleanUrl);
                        }
                    } catch (e) {
                        // Ignore URL parsing issues in old browsers
                    }
                }

                function closePopup() {
                    popup.classList.remove('show');
                    setTimeout(function () {
                        if (popup && popup.parentNode) {
                            popup.parentNode.removeChild(popup);
                        }
                        cleanContactSuccessQuery();
                    }, 220);
                }

                if (closeBtn) {
                    closeBtn.addEventListener('click', closePopup);
                }
                popup.addEventListener('click', function (event) {
                    if (event.target === popup) {
                        closePopup();
                    }
                });

                setTimeout(closePopup, 5000);
            });
        </script>
    <?php endif; ?>

    <!-- opening hours (after contact) -->

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

    <!-- footer sections -->
    <?php
    include('footer.php');
    ?>
