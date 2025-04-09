<?php

include('header.php');
include('sidebar.php');
include('connect.php');


$haircut_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$haircut_query = "SELECT * FROM haircut_service  WHERE hair_id = $haircut_id";
$haircut_result = $con->query($haircut_query);
$haircut_row = mysqli_fetch_assoc($haircut_result);

if(isset($_POST['haircut-btn'])) {
        $haircut_id = $_POST['haircut_id'];
        $haircut_service = $_POST['haircut_service'];
        $haircut_price = $_POST['haircut_price'];
        $update_query = "UPDATE haircut_service SET hair_service = '$haircut_service', hair_price = '$haircut_price' WHERE hair_id = $haircut_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:service_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 

// beard 

$beard_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$beard_query = "SELECT * FROM beard_service  WHERE beard_id = $beard_id";
$beard_result = $con->query($beard_query);
$beard_row = mysqli_fetch_assoc($beard_result);

if(isset($_POST['beard-btn'])) {
        $beard_id = $_POST['beard_id'];
        $beard_service = $_POST['beard_service'];
        $beard_price = $_POST['beard_price'];
        $update_query = "UPDATE beard_service SET beard_service = '$beard_service', beard_price = '$beard_price' WHERE beard_id = $beard_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:service_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 

// skin

$skin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$skin_query = "SELECT * FROM skin_service  WHERE skin_id = $skin_id";
$skin_result = $con->query($skin_query);
$skin_row = mysqli_fetch_assoc($skin_result);

if(isset($_POST['skin-btn'])) {
        $skin_id = $_POST['skin_id'];
        $skin_service = $_POST['skin_service'];
        $skin_price = $_POST['skin_price'];
        $update_query = "UPDATE skin_service SET skin_service = '$skin_service', skin_price = '$skin_price' WHERE skin_id = $skin_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:service_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 



// spa 

$spa_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$spa_query = "SELECT * FROM spa_service  WHERE spa_id = $spa_id";
$spa_result = $con->query($spa_query);
$spa_row = mysqli_fetch_assoc($spa_result);

if(isset($_POST['spa-btn'])) {
        $spa_id = $_POST['spa_id'];
        $spa_service = $_POST['spa_service'];
        $spa_price = $_POST['spa_price'];
        $update_query = "UPDATE spa_service SET spa_service = '$spa_service', spa_price = '$spa_price' WHERE spa_id = $spa_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:service_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Membership</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="main-content">
        <div class="content">
            <h1>Update Hair Services</h1>
            <form class="product-form" action="update_service.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="haircut_id" value="<?php echo $haircut_row["hair_id"]; ?>">

                <label for="haircut_service">Enter Service:</label>
                <input type="text" id="haircut_service" name="haircut_service" value="<?php echo $haircut_row["hair_service"]; ?>">

                <label for="haircut_price">Enter Price:</label>
                <input type="text" id="haircut_price" name="haircut_price" value="<?php echo $haircut_row["hair_price"];?>">
                <div class="membership-upd-btn">
                    <button name="haircut-btn" onclick="return //confirm('Updated Successfully..!!');">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

<div class="main-content">
        <div class="content">
            <h1>Update Beard Services</h1>
            <form class="product-form" action="update_service.php" method="post" enctype="multipart/form-data">
                
                <input type="hidden" name="beard_id" value="<?php echo $beard_row["beard_id"]; ?>">

                <label for="beard_service">Enter Service:</label>
                <input type="text" id="beard_service" name="beard_service" value="<?php echo $beard_row["beard_service"]; ?>">

                <label for="beard_price">Enter Price:</label>
                <input type="text" id="beard_price" name="beard_price" value="<?php echo $beard_row["beard_price"];?>">
                <div class="membership-upd-btn">
                    <button name="beard-btn">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

<div class="main-content">
        <div class="content">
            <h1>Update Skin Services</h1>
            <form class="product-form" action="update_service.php" method="post" enctype="multipart/form-data">
                
                <input type="hidden" name="skin_id" value="<?php echo $skin_row["skin_id"]; ?>">

                <label for="skin_service">Enter Service:</label>
                <input type="text" id="skin_service" name="skin_service" value="<?php echo $skin_row["skin_service"]; ?>">

                <label for="skin_price">Enter Price:</label>
                <input type="text" id="skin_price" name="skin_price" value="<?php echo $skin_row["skin_price"];?>">
                <div class="membership-upd-btn">
                    <button name="skin-btn">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

<div class="main-content">
        <div class="content">
            <h1>Update Spa Services</h1>
            <form class="product-form" action="update_service.php" method="post" enctype="multipart/form-data">
                
                <input type="hidden" name="spa_id" value="<?php echo $spa_row["spa_id"]; ?>">

                <label for="spa_service">Enter Service:</label>
                <input type="text" id="spa_service" name="spa_service" value="<?php echo $spa_row["spa_service"]; ?>">

                <label for="spa_price">Enter Price:</label>
                <input type="text" id="spa_price" name="spa_price" value="<?php echo $spa_row["spa_price"];?>">
                <div class="membership-upd-btn">
                    <button name="spa-btn">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

</body>
</html>