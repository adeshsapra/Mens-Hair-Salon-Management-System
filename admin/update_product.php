<?php 

include('header.php'); 
include('connect.php');
require_once('page_header_helper.php');

$category_table_ready = false;
$category_column_ready = false;
$product_categories = [];

$category_table_check = mysqli_query($con, "SHOW TABLES LIKE 'product_categories'");
if ($category_table_check && mysqli_num_rows($category_table_check) > 0) {
    $category_table_ready = true;
}

$category_column_check = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'category_id'");
if ($category_column_check && mysqli_num_rows($category_column_check) > 0) {
    $category_column_ready = true;
}

if ($category_table_ready) {
    $category_result = mysqli_query($con, 'SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC');
    if ($category_result) {
        while ($category_row = mysqli_fetch_assoc($category_result)) {
            $product_categories[] = $category_row;
        }
    }
}

$product_id = 0;
if (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];
} elseif (isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
}

$query = "SELECT * FROM products WHERE p_id = $product_id";
$all_product = $con->query($query);
$row = mysqli_fetch_assoc($all_product);

if (!$row) {
    header('Location:products.php?toast=error&msg=Product+not+found');
    exit();
}

if (isset($_POST['update'])) {

    $product_id = intval($_POST['product_id']);
    $update_parts = [];

    if (isset($_POST['product_name']) && $_POST['product_name'] !== '') {
        $p_name = mysqli_real_escape_string($con, $_POST['product_name']);
        $update_parts[] = "p_name='$p_name'";
    }

    if (isset($_POST['product_desc']) && $_POST['product_desc'] !== '') {
        $p_desc = mysqli_real_escape_string($con, $_POST['product_desc']);
        $update_parts[] = "p_desc='$p_desc'";
    }
    
    if (isset($_POST['product_price']) && $_POST['product_price'] !== '') {
        $p_price = (float) $_POST['product_price'];
        $update_parts[] = "p_price='$p_price'";
    }
    
    if (isset($_POST['product_discount']) && $_POST['product_discount'] !== '') {
        $p_discount = max(0, min(100, (float) $_POST['product_discount']));
        $update_parts[] = "p_discount='$p_discount'";
    }

    if (isset($_POST['product_size']) && $_POST['product_size'] !== '') {
        $p_size = mysqli_real_escape_string($con, $_POST['product_size']);
        $update_parts[] = "p_size='$p_size'";
    }

    if (isset($_POST['product_overview']) && $_POST['product_overview'] !== '') {
        $p_overview = mysqli_real_escape_string($con, $_POST['product_overview']);
        $update_parts[] = "p_overview='$p_overview'";
    }

    if (isset($_POST['product_f1']) && $_POST['product_f1'] !== '') {
        $p_f1 = mysqli_real_escape_string($con, $_POST['product_f1']);
        $update_parts[] = "p_f1='$p_f1'";
    }

    if (isset($_POST['product_f2']) && $_POST['product_f2'] !== '') {
        $p_f2 = mysqli_real_escape_string($con, $_POST['product_f2']);
        $update_parts[] = "p_f2='$p_f2'";
    }

    if (isset($_POST['product_ingred']) && $_POST['product_ingred'] !== '') {
        $p_ingred = mysqli_real_escape_string($con, $_POST['product_ingred']);
        $update_parts[] = "p_ingred='$p_ingred'";
    }

    if (isset($_POST['product_quantity']) && $_POST['product_quantity'] !== '') {
        $p_quantity = (int) $_POST['product_quantity'];
        $update_parts[] = "p_quantity='$p_quantity'";
    }

    if ($category_column_ready && isset($_POST['product_category_id'])) {
        $category_id = (int) $_POST['product_category_id'];
        if ($category_id > 0) {
            $update_parts[] = "category_id=$category_id";
        } else {
            $update_parts[] = 'category_id=NULL';
        }
    }
  
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        
        $p_image = mysqli_real_escape_string($con, $_FILES['product_image']['name']);
        $p_image_size=$_FILES['product_image']['size'];
        $p_image_tmp=$_FILES['product_image']['tmp_name'];
        $p_image_folder = '../upload_product_photos/' . $_FILES['product_image']['name'];
        
        if (move_uploaded_file($p_image_tmp,$p_image_folder)) {
            $update_parts[] = "p_img='$p_image'";
        }
    }

    
    if (!empty($update_parts)) {
        $update_query = "UPDATE products SET " . implode(', ', $update_parts) . " WHERE p_id=$product_id";
        
        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:products.php?toast=success&msg=Product+updated+successfully!");
            exit();
        } else {
            echo "Error updating product: " . $con->error;
        }
    }
}

$query = "SELECT * FROM products WHERE p_id = $product_id";
$all_product = $con->query($query);
$row = mysqli_fetch_assoc($all_product);

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
    <?php
    renderAdminPageIntro(
        'Products / Update Product',
        'Update Product Details',
        'Edit product information, pricing, stock, and media to keep catalog data accurate.'
    );
    ?>

    <div class="main-content">
        <div class="content">

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

            <form class="product-form" action="update_product.php?id=<?php echo (int) $row["p_id"]; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo (int) $row["p_id"]; ?>">

                <label for="product-name">Product Name:</label>
                <input type="text" id="product-name" name="product_name" value="<?php echo htmlspecialchars($row["p_name"]);?>">

                <label for="product-description">Description:</label>
                <textarea id="product-description" name="product_desc" rows="2"><?php echo htmlspecialchars($row["p_desc"]);?></textarea>

                <label for="product-category">Category:</label>
                <select id="product-category" name="product_category_id">
                    <option value="">Select Category (Optional)</option>
                    <?php foreach ($product_categories as $category): ?>
                        <?php
                            $selected_id = isset($row['category_id']) ? (int) $row['category_id'] : 0;
                            $is_selected = $selected_id === (int) $category['category_id'];
                        ?>
                        <option value="<?php echo (int) $category['category_id']; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$category_table_ready || !$category_column_ready): ?>
                    <small class="category-field-hint">Category setup pending. Run <code>admin/setup_product_categories_table.php</code> once.</small>
                <?php endif; ?>

                <label for="product-price">Price:</label>
                <input type="text" id="product-price" name="product_price"  value="<?php echo htmlspecialchars($row["p_price"]);?>">

                <label for="product-discount">Discount (%):</label>
                <input type="number" id="product-discount" name="product_discount" min="0" max="100" step="0.01" value="<?php echo isset($row["p_discount"]) ? htmlspecialchars($row["p_discount"]) : 0; ?>">

                <label for="product-final-price">Final Price After Discount:</label>
                <input type="text" id="product-final-price" readonly>

                <label for="product-size">Size:</label>
                <input type="text" id="product-size" name="product_size"  value="<?php echo htmlspecialchars($row["p_size"]);?>">

                <h2>Product Details:</h2>
                <label for="product-overview">Overview:</label>
                <textarea id="product-overview" name="product_overview" rows="2"><?php echo htmlspecialchars($row["p_overview"]);?></textarea>
                <h3>features: </h3>
                <label for="product-feature1">Line1:</label>
                <textarea id="product-feature1" name="product_f1" rows="1"><?php echo htmlspecialchars($row["p_f1"]);?></textarea>
                <label for="product-feature2">Line1:</label>
                <textarea id="product-feature2" name="product_f2" rows="1"><?php echo htmlspecialchars($row["p_f2"]);?></textarea>

                <label for="product-ingred">Ingredients:</label>
                <textarea id="product-ingred" name="product_ingred" rows="2"><?php echo htmlspecialchars($row["p_ingred"]);?></textarea>
                

                <label for="product-image">Product Image:</label>
                <input type="file" id="product-image" name="product_image" accept=".jpg .jpeg .png" onchange="previewImage(event)">

                <div id="image-preview-container">
                    <img id="image-preview" src="../upload_product_photos/<?php echo htmlspecialchars($row["p_img"]);?>" alt="Image Preview" style="display: block;">
                </div>
                
                <label for="product-quantity">Product Quantity:</label>
                <input type="number" id="product-quantity" name="product_quantity"  value="<?php echo (int) $row["p_quantity"];?>">

                <button type="submit" class="add-product" name="update">Update Product</button>
            </form>
        </div>
    </div>

    <script>
    function updateFinalPricePreview() {
        const basePrice = parseFloat(document.getElementById('product-price').value || 0);
        const discount = parseFloat(document.getElementById('product-discount').value || 0);
        const clampedDiscount = Math.max(0, Math.min(100, discount));
        const finalPrice = basePrice - ((basePrice * clampedDiscount) / 100);
        document.getElementById('product-final-price').value = finalPrice.toFixed(2);
    }

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
            preview.src = '../upload_product_photos/<?php echo htmlspecialchars($row['p_img']); ?>';
            preview.style.display = 'block';
            container.style.display = 'block';
        }
    }

    document.getElementById('product-price').addEventListener('input', updateFinalPricePreview);
    document.getElementById('product-discount').addEventListener('input', updateFinalPricePreview);
    updateFinalPricePreview();
    </script>
</body>
</html>


