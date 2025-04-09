<?php 

include('header.php'); 
include('connect.php');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM products WHERE p_id = $product_id";
$all_product = $con->query($query);
$row = mysqli_fetch_assoc($all_product);

if (isset($_POST['update'])) {

    $product_id = intval($_POST['product_id']);
    $update_parts = [];

    if (!empty($_POST['product_name'])) {
        $p_name = $_POST['product_name'];
        $update_parts[] = "p_name='$p_name'";
    }

    if (!empty($_POST['product_desc'])) {
        $p_desc = $_POST['product_desc'];
        $update_parts[] = "p_desc='$p_desc'";
    }
    
    if (!empty($_POST['product_price'])) {
        $p_price = $_POST['product_price'];
        $update_parts[] = "p_price='$p_price'";
    }

    if (!empty($_POST['product_size'])) {
        $p_size = $_POST['product_size'];
        $update_parts[] = "p_size='$p_size'";
    }

    if (!empty($_POST['product_overview'])) {
        $p_overview = $_POST['product_overview'];
        $update_parts[] = "p_overview='$p_overview'";
    }

    if (!empty($_POST['product_f1'])) {
        $p_f1 =$_POST['product_f1'];
        $update_parts[] = "p_f1='$p_f1'";
    }

    if (!empty($_POST['product_f2'])) {
        $p_f2 =$_POST['product_f2'];
        $update_parts[] = "p_f2='$p_f2'";
    }

    if (!empty($_POST['product_ingred'])) {
        $p_ingred = $_POST['product_ingred'];
        $update_parts[] = "p_ingred='$p_ingred'";
    }

    if (!empty($_POST['product_quantity'])) {
        $p_quantity = $_POST['product_quantity'];
        $update_parts[] = "p_quantity='$p_quantity'";
    }
  
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        
        $p_image=$_FILES['product_image']['name'];
        $p_image_size=$_FILES['product_image']['size'];
        $p_image_tmp=$_FILES['product_image']['tmp_name'];
        $p_image_folder='../upload_product_photos/'.$p_image;
        
        if (move_uploaded_file($p_image_tmp,$p_image_folder)) {
            $update_parts[] = "p_img='$p_image_folder'";
        }
    }

    
    if (!empty($update_parts)) {
        $update_query = "UPDATE products SET " . implode(', ', $update_parts) . " WHERE p_id=$product_id";
        
        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:products.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
    } else {
        echo "No fields to update.";
    }
} else {
    echo "No data submitted.";
}

$con->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <div class="main-content">
        <div class="content">
            <h1>Update Product</h1>

            <?php
                        if(isset($message))
                        {
                            foreach($message as $message)
                            {
                                echo'<div class="message">'.$message.'</div>';
                            }
                        }
                        if(isset($confirm))
                        {
                            foreach($confirm as $confirm)
                            {
                                echo'<div class="confirm">'.$confirm.'</div>';
                            }
                        }
                        
            ?>

            <form class="product-form" action="update_product.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $row["p_id"]; ?>">

                <label for="product-name">Product Name:</label>
                <input type="text" id="product-name" name="product_name" value="<?php echo $row["p_name"];?>">

                <label for="product-description">Description:</label>
                <textarea id="product-description" name="product_desc" rows="2"><?php echo $row["p_desc"];?></textarea>

                <label for="product-price">Price:</label>
                <input type="text" id="product-price" name="product_price"  value="<?php echo $row["p_price"];?>">

                <label for="product-size">Size:</label>
                <input type="text" id="product-size" name="product_size"  value="<?php echo $row["p_size"];?>">

                <h2>Product Details:</h2>
                <label for="product-overview">Overview:</label>
                <textarea id="product-overview" name="product_overview" rows="2"><?php echo $row["p_overview"];?></textarea>
                <h3>features: </h3>
                <label for="product-feature1">Line1:</label>
                <textarea id="product-feature1" name="product_f1" rows="1"><?php echo $row["p_f1"];?></textarea>
                <label for="product-feature2">Line1:</label>
                <textarea id="product-feature2" name="product_f2" rows="1">  <?php echo $row["p_f2"];?></textarea>

                <label for="product-ingred">Ingredients:</label>
                <textarea id="product-ingred" name="product_ingred" rows="2"><?php echo $row["p_ingred"];?></textarea>
                

                <label for="product-image">Product Image:</label>
                <input type="file" id="product-image" name="product_image" accept=".jpg .jpeg .png" onchange="previewImage(event)">

                <div id="image-preview-container">
                    <img id="image-preview" src="<?php echo $row["p_img"];?>" alt="Image Preview" style="display: none;">
                </div>
                
                <label for="product-quantity">Product Quantity:</label>
                <input type="number" id="product-quantity" name="product_quantity"  value="<?php echo $row["p_quantity"];?>">

                <button type="submit" class="add-product" name="update">Update Product</button>
            </form>
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
            preview.src = '<?php echo $row['p_img']; ?>';
            preview.style.display = 'block';
            container.style.display = 'block';
        }
    }
    </script>
</body>
</html>
