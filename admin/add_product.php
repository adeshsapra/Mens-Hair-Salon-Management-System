<?php

include('header.php');
include('sidebar.php');
include('connect.php');
if(isset($_POST['add-product'])){

    $p_name = $_POST['product_name'];
    $p_desc = $_POST['product_desc'];
    $p_price = $_POST['product_price'];
    $p_size = $_POST['product_size'];
    $p_overview = $_POST['product_overview'];
    $p_f1 = $_POST['product_f1'];
    $p_f2 = $_POST['product_f2'];
    $p_ingred = $_POST['product_ingred'];
    $p_quantity = $_POST['product_quantity'];


    $p_image=$_FILES['product_image']['name'];
    $p_image_size=$_FILES['product_image']['size'];
    $p_image_tmp=$_FILES['product_image']['tmp_name'];
    $p_image_folder='../upload_product_photos/'.$p_image;

    if(empty($p_name) || empty($p_desc) || empty($p_price) || empty($p_size) || empty($p_overview) ||empty($p_image) ||empty($p_quantity) ){
        $message[]="Please Fill Out All Details..!!";
    }
    else{
        if ($_FILES["product_image"]["size"] > 5000000) {
                    $message= "Sorry, your file is too large..!!";
        }
        else{
            $insert=mysqli_query($con,"insert into products(p_name,p_desc,p_price,p_size,p_overview,p_f1,p_f2,p_ingred,p_img,p_quantity)
            values('$p_name','$p_desc','$p_price','$p_size','$p_overview','$p_f1','$p_f2','$p_ingred','$p_image','$p_quantity')") or die('Query Failed');
    
            if($insert)
            {
                move_uploaded_file($p_image_tmp,$p_image_folder);
                $confirm[]='New Product Add Sucessfully..!';
                header("Location:products.php");
            }
            else{
                $confirm[]='Could Not Add The Product..!';
            }
        }
    }
}

?>


<div class="main-content">
    <div class="content">
        <h1>Add New Products</h1>
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
                <input type="text" id="product-name" name="product_name">

                <label for="product-description">Description:</label>
                <textarea id="product-description" name="product_desc" rows="2"></textarea>

                <label for="product-price">Price:</label>
                <input type="number" id="product-price" name="product_price">

                <label for="product-size">Size:</label>
                <input type="text" id="product-size" name="product_size">

                <h2>Product Details:</h2>
                <label for="product-overview">Overview:</label>
                <textarea id="product-overview" name="product_overview" rows="2"></textarea>
                <h3>features: </h3>
                <label for="product-feature1">Line1:</label>
                <textarea id="product-feature1" name="product_f1" rows="1"></textarea>
                <label for="product-feature2">Line1:</label>
                <textarea id="product-feature2" name="product_f2" rows="1"></textarea>

                <label for="product-ingred">Ingredients:</label>
                <textarea id="product-ingred" name="product_ingred" rows="2"></textarea>
                

                <label for="product-image">Product Image:</label>
                <input type="file" id="product-image" name="product_image" accept=".jpg .jpeg .png">

                <div id="image-preview-container">
                    <img id="image-preview" src="#" alt="Image Preview" style="display: none;">
                </div>
                
                <label for="product-quantity">Product Quantity:</label>
                <input type="number" id="product-quantity" name="product_quantity">

                <button type="submit" name="add-product" class="add-product">Add Product</button>
            </form>
        </div>
</div>
</div>

