<?php

include('header.php');
include('sidebar.php');
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

if(isset($_POST['add-product'])){

    $p_name = $_POST['product_name'];
    $p_desc = $_POST['product_desc'];
    $p_price = (float) $_POST['product_price'];
    $p_discount = isset($_POST['product_discount']) ? max(0, min(100, (float) $_POST['product_discount'])) : 0;
    $p_size = $_POST['product_size'];
    $p_overview = $_POST['product_overview'];
    $p_f1 = $_POST['product_f1'];
    $p_f2 = $_POST['product_f2'];
    $p_ingred = $_POST['product_ingred'];
    $p_quantity = (int) $_POST['product_quantity'];
    $selected_category_id = isset($_POST['product_category_id']) ? (int) $_POST['product_category_id'] : 0;


    $p_image=$_FILES['product_image']['name'];
    $p_image_size=$_FILES['product_image']['size'];
    $p_image_tmp=$_FILES['product_image']['tmp_name'];
    $p_image_folder='../upload_product_photos/'.$p_image;

    if(empty($p_name) || empty($p_desc) || empty($p_price) || empty($p_size) || empty($p_overview) ||empty($p_image) ||empty($p_quantity) ){
        $message[]="Please Fill Out All Details..!!";
    }
    else{
        if ($_FILES["product_image"]["size"] > 5000000) {
                    $message[]= "Sorry, your file is too large..!!";
        }
        else{
            $p_name_db = mysqli_real_escape_string($con, $p_name);
            $p_desc_db = mysqli_real_escape_string($con, $p_desc);
            $p_size_db = mysqli_real_escape_string($con, $p_size);
            $p_overview_db = mysqli_real_escape_string($con, $p_overview);
            $p_f1_db = mysqli_real_escape_string($con, $p_f1);
            $p_f2_db = mysqli_real_escape_string($con, $p_f2);
            $p_ingred_db = mysqli_real_escape_string($con, $p_ingred);
            $p_image_db = mysqli_real_escape_string($con, $p_image);

            if ($category_column_ready) {
                $category_value_sql = $selected_category_id > 0 ? (string) $selected_category_id : 'NULL';
                $insert_query = "INSERT INTO products(category_id,p_name,p_desc,p_price,p_discount,p_size,p_overview,p_f1,p_f2,p_ingred,p_img,p_quantity)
                VALUES($category_value_sql,'$p_name_db','$p_desc_db','$p_price','$p_discount','$p_size_db','$p_overview_db','$p_f1_db','$p_f2_db','$p_ingred_db','$p_image_db','$p_quantity')";
            } else {
                $insert_query = "INSERT INTO products(p_name,p_desc,p_price,p_discount,p_size,p_overview,p_f1,p_f2,p_ingred,p_img,p_quantity)
                VALUES('$p_name_db','$p_desc_db','$p_price','$p_discount','$p_size_db','$p_overview_db','$p_f1_db','$p_f2_db','$p_ingred_db','$p_image_db','$p_quantity')";
            }

            $insert=mysqli_query($con, $insert_query) or die('Query Failed');
    
            if($insert)
            {
                move_uploaded_file($p_image_tmp,$p_image_folder);
                $confirm[]='New Product Add Sucessfully..!';
                header("Location:products.php?toast=success&msg=New+product+added+successfully!");
            }
            else{
                $confirm[]='Could Not Add The Product..!';
            }
        }
    }
}

?>


<?php
renderAdminPageIntro(
    'Products / Add Product',
    'Create New Product',
    'Add a new product with pricing, stock, rich details, and image assets for storefront readiness.'
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
        <div class="product-form-container">
            <form class="product-form" action="" method="post" enctype="multipart/form-data">
                <label for="product-name">Product Name:</label>
                <input type="text" id="product-name" name="product_name" value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">

                <label for="product-description">Description:</label>
                <textarea id="product-description" name="product_desc" rows="2"><?php echo isset($_POST['product_desc']) ? htmlspecialchars($_POST['product_desc']) : ''; ?></textarea>

                <label for="product-category">Category:</label>
                <select id="product-category" name="product_category_id">
                    <option value="">Select Category (Optional)</option>
                    <?php foreach ($product_categories as $category): ?>
                        <?php $is_selected = isset($_POST['product_category_id']) && (int) $_POST['product_category_id'] === (int) $category['category_id']; ?>
                        <option value="<?php echo (int) $category['category_id']; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$category_table_ready || !$category_column_ready): ?>
                    <small class="category-field-hint">Category setup pending. Run <code>admin/setup_product_categories_table.php</code> once.</small>
                <?php endif; ?>

                <label for="product-price">Price:</label>
                <input type="number" id="product-price" name="product_price" value="<?php echo isset($_POST['product_price']) ? htmlspecialchars($_POST['product_price']) : ''; ?>">

                <label for="product-discount">Discount (%):</label>
                <input type="number" id="product-discount" name="product_discount" min="0" max="100" step="0.01" value="<?php echo isset($_POST['product_discount']) ? htmlspecialchars($_POST['product_discount']) : '0'; ?>">

                <label for="product-final-price">Final Price After Discount:</label>
                <input type="text" id="product-final-price" readonly>

                <label for="product-size">Size:</label>
                <input type="text" id="product-size" name="product_size" value="<?php echo isset($_POST['product_size']) ? htmlspecialchars($_POST['product_size']) : ''; ?>">

                <h2>Product Details:</h2>
                <label for="product-overview">Overview:</label>
                <textarea id="product-overview" name="product_overview" rows="2"><?php echo isset($_POST['product_overview']) ? htmlspecialchars($_POST['product_overview']) : ''; ?></textarea>
                <h3>features: </h3>
                <label for="product-feature1">Line1:</label>
                <textarea id="product-feature1" name="product_f1" rows="1"><?php echo isset($_POST['product_f1']) ? htmlspecialchars($_POST['product_f1']) : ''; ?></textarea>
                <label for="product-feature2">Line1:</label>
                <textarea id="product-feature2" name="product_f2" rows="1"><?php echo isset($_POST['product_f2']) ? htmlspecialchars($_POST['product_f2']) : ''; ?></textarea>

                <label for="product-ingred">Ingredients:</label>
                <textarea id="product-ingred" name="product_ingred" rows="2"><?php echo isset($_POST['product_ingred']) ? htmlspecialchars($_POST['product_ingred']) : ''; ?></textarea>
                

                <label for="product-image">Product Image:</label>
                <input type="file" id="product-image" name="product_image" accept=".jpg .jpeg .png">

                <div id="image-preview-container">
                    <img id="image-preview" src="#" alt="Image Preview" style="display: none;">
                </div>
                
                <label for="product-quantity">Product Quantity:</label>
                <input type="number" id="product-quantity" name="product_quantity" value="<?php echo isset($_POST['product_quantity']) ? htmlspecialchars($_POST['product_quantity']) : ''; ?>">

                <button type="submit" name="add-product" class="add-product">Add Product</button>
            </form>
        </div>
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

    document.getElementById('product-price').addEventListener('input', updateFinalPricePreview);
    document.getElementById('product-discount').addEventListener('input', updateFinalPricePreview);
    updateFinalPricePreview();
</script>



