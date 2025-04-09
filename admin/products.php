<?php

include('header.php');
// include('sidebar.php');
include('connect.php');

$query = "SELECT * FROM products";
$all_product = $con->query($query);
?>

<div class="main-content">
    <div class="content">
        <h1>Products</h1>
        <p>Manage salon products here.</p>

         <div class="right">
         <div class="product-button">
            <a href="add_product.php">Add New Product</a>
        </div>
         </div>
       

        <!-- Product List -->
        <div class="product-list">
            <h2>Existing Products</h2>
            <?php 
                while($row = mysqli_fetch_assoc($all_product)){
            ?>
                    <div class="product-item">
                        <img src="../upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product Image">
                        <div class="product-info">
                            <h2><?php echo $row["p_name"]; ?></h2>
                            <p><?php echo $row["p_desc"]; ?></p>
                            <p>₹ <?php echo $row["p_price"]; ?> <i> ( <?php echo $row["p_size"]; ?> )</i></p>
                            <p>Quantity: <?php echo $row["p_quantity"]; ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="update_product.php?id=<?php echo $row["p_id"]; ?>">
                                <button class="update">Edit</button>
                            </a>
                            <a href="delete_product.php?id=<?php echo $row["p_id"]; ?>">
                                <button class="delete">Delete</button>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                    ?>
                <p>No products found.</p>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
        container.style.display = 'none';
    }
}
</script>

