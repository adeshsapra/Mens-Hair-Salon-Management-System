<?php 
include('header.php'); 
include('sidebar.php'); 
include('connect.php'); 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM products WHERE p_id = $id";
$all_product = $con->query($query);

$row = mysqli_fetch_assoc($all_product);

if(isset($_POST['delete'])){
    mysqli_query($con, "DELETE FROM products WHERE p_id = $id");
    header("Location:products.php");
    $con->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-content">
        <div class="content">
            <h1>Delete Product</h1>
            <div class="delete-confirmation">
                <h2>Are you sure you want to delete the following product?</h2>
                <?php 
                //if($row = mysqli_fetch_assoc($all_product)){
            ?>
                <div class="product-item-delete">
                    <img src="../upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product Image">
                    <div class="product-info">
                        <h2><?php  echo $row["p_name"]; ?> </h2>
                        <p>₹ <?php echo $row["p_price"]; ?> <i> ( <?php echo $row["p_size"]; ?> )</i></p>
                    </div>
                </div>
                <?php
                // }else{
                //     echo "Product Not Found";
                // }   
                
                ?>
                <form action="delete_product.php?id=<?php echo $row["p_id"]; ?>" method="post">
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this product?');">Confirm Delete</button>
                    <a href="products.php" class="cancel-button">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
