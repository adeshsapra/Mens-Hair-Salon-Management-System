<?php
include('connect.php');
$query="SELECT * FROM products";
$all_product= $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut Eshop</title>

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
            <h2>Men's Grooming Products</h2>
            <div clsas="menu">
                <a href="index.php">HOME</a> / <span>Our E-shop Products</span>
            </div>
           
        </div>
        
     </div>


    <!-- default section -->

    <!-- /* product section */ -->

     <div class="product-main-container">
        <div class="product-container">
            
            <!-- <div class="product-card">
                <img src="products/hairpowder.jpg" alt="Product 2">
                <h3>Hair Volumizing Powder</h3>
                <p class="content">ClassyCut's volumizing powder wax adds instant lift and texture with a lightweight, natural feel.</p>
                <p>₹ 349 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairoil.jpg" alt="Product 2">
                <h3>Hair Oil</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 299 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairsprey.jpg" alt="Product 3">
                <h3>Hair Spray</h3>
                <p class="content"> ClassyCut's Strong Hold Hair Spray, a fast-drying, non-sticky formula that keeps your look in place all day.</p>
                <p>₹ 499 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/wax.jpg" alt="Product 4">
                <h3>Hair Wax</h3>
                <p class="content">ClassyCut's provides hair wax delivers a strong, lexible hold with, matte texture for all-day style.</p>
                <p>₹ 699 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/conditioner.jpg" alt="Product 2">
                <h3>Hair Conditioner</h3>
                <p class="content">classycut's hair conditioner is smooths, detangles and leaving it soft and shiny.</p>
                <p>₹ 199 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>
            
            <div class="product-card">
                <img src="products/shampoo.png" alt="Product 4">
                <h3>Hair Shampoo</h3>
                <p class="content">ClassyCut's shampoo deeply cleanses and hydrates for soft, healthy, and manageable hair.</p>
                <p>₹ 399 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>
            
            <div class="product-card">
                <img src="products/serum.jpg" alt="Product 4">
                <h3>Hair Serum</h3>
                <p class="content">ClassyCut's Hair Serum  a lightweight, shine, and protects your hair from heat and damage.</p>
                <p>₹ 499 <i> (50ml)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairjel.jpg" alt="Product 4">
                <h3>Hair gel</h3>
                <p class="content">ClassyCut's provides hair gel offers firm control and a smooth, residue-free shine for any style.</p>
                <p>₹ 249 <i> (50g)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/facewash.jpg" alt="Product 2">
                <h3>Face Wash</h3>
                <p class="content">ClassyCut's Face Wash gently cleanses and balances your skin, removing impurities for a refreshed and glow.</p>
                <p>₹ 499 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/facecream.jpg" alt="Product 4">
                <h3>Face Cream</h3>
                <p class="content">ClassyCut's hydrating face cream deeply moisturizes and rejuvenates skin for a radiant, youthful glow.</p>
                <p>₹ 199 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>
        
            <div class="product-card">
                <img src="products/beardoil2.jpg" alt="Product 4">
                <h3>Beard Oil</h3>
                <p class="content">ClassyCut's beard oil conditions and softens for a well-groomed, smooth beard with a subtle shine.</p>
                <p>₹ 499 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/beardcream.jpg" alt="Product 4">
                <h3>Beard Cream</h3>
                <p class="content">
                    ClassyCut's beard cream tames and hydrates your beard, ensuring a smooth, polished look with every use.
                </p>
                <p>₹ 799 <i> ( 100g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/goldmask.jpg" alt="Product 4">
                <h3>Golden Face Mask</h3>
                <p class="content">ClassyCut's Gold Mask delivers a golden touch of luxury, illuminating your skin for a radiant glow.</p>
                <p>₹ 1999 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/silvermask.jpg" alt="Product 4">
                <h3>Silver Face Mask</h3>
                <p class="content">ClassyCut's Silver Mask revitalizes your skin with a premium silver formula for a luminous, sophisticated glow.</p>
                <p>₹ 1499 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/charcolmask.jpg" alt="Product 4">
                <h3>Charcol Face Mask</h3>
                <p class="content">ClassyCut's Charcoal Facial Mask detoxifies and purifies for a clear and refreshed complexion.</p>
                <p>₹ 999 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/vitaminmask.jpg" alt="Product 4">
                <h3>Vitamin-c Face Mask</h3>
                <p class="content">ClassyCut's Vitamin C Face mask brightens and energizes your skin, revealing a radiant and youthful complexion.</p>
                <p>₹ 599 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div> -->

            <!-- display product -->
            <?php
                while($row = mysqli_fetch_assoc($all_product)){
            ?>
             <div class="product-card">
                <img src="upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product 4">
                <h3><?php echo $row["p_name"]; ?></h3>
                <p class="content"><?php echo $row["p_desc"]; ?></p>
                <p>₹ <?php echo $row["p_price"]; ?> <i> ( <?php echo $row["p_size"]; ?> )</i></p>
                <a href="product_display.php?id=<?php echo $row["p_id"]; ?>">
                        <button>View Details</button>
                </a>
            </div>
            <?php
                }
            ?>

        </div>
        <h1><i class="fas fa-lock"></i> Unlock premium Combo's</h1>
        <p> "Unlock the ultimate value and elevate your experience with our premium combo, offering unbeatable quality and luxury in one exclusive package."</p>

        <div class="product-container">

            <div class="product-card">
                <img src="products/haircombo.jpg" alt="Product 4">
                <h3>Crown Of Elegance HairCare Collection</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 1999 </p>
                <button>View Details</button>
            </div>

            <div class="product-card">
                <img src="products/facecombo.jpg" alt="Product 4">
                <h3>Ultimate Elegance Skincare Collection</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 2499</p>
                <button>View Details</button>
            </div>

            <div class="product-card">
                <img src="products/beardcombo.jpg" alt="Product 4">
                <h3>Regal Beard Masterpiece Collection</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 1199 </p>
                <button>View Details</button>
            </div>

            <div class="product-card">
                <img src="products/maskcombo.jpg" alt="Product 4">
                <h3>Luxurious Glow Masque Collection</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 2999 </p>
                <button>View Details</button>
            </div>
        </div>
    </div>
    <!-- /* product section */ -->


    <!-- footer sections -->

   
    <?php 
          include('footer.php');
     ?>


    <!-- footer sections -->

    <script src="js/script.js"></script>
</body>

</html>