<?php

include('header.php');
include('sidebar.php');
include('connect.php');


$royal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$royal_query = "SELECT * FROM royal_membership WHERE royal_id = $royal_id";
$royal_result = $con->query($royal_query);
$royal_row = mysqli_fetch_assoc($royal_result);

if(isset($_POST['royal-btn'])) {
        $royal_id = $_POST['royal_id'];
        $royal_desc = $_POST['royal_desc'];
        $royal_price = $_POST['royal_price'];
        $update_query = "UPDATE royal_membership SET royal_desc = '$royal_desc', royal_price = '$royal_price' WHERE royal_id = $royal_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:membership_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 

// Classic
$classic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$classic_query = "SELECT * FROM classic_membership WHERE classic_id = $classic_id";
$classic_result = $con->query($classic_query);
$classic_row = mysqli_fetch_assoc($classic_result);

if(isset($_POST['classic-btn'])) {
        $classic_id = $_POST['classic_id'];
        $classic_desc = $_POST['classic_desc'];
        $classic_price = $_POST['classic_price'];
        $update_query = "UPDATE classic_membership SET classic_desc = '$classic_desc', classic_price = '$classic_price' WHERE classic_id = $classic_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:membership_manage.php");
        } else {
            echo "Error updating product: " . $con->error;
        }
} 

// standard
$standard_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$standard_query = "SELECT * FROM standard_membership WHERE standard_id = $standard_id";
$standard_result = $con->query($standard_query);
$standard_row = mysqli_fetch_assoc($standard_result);

if(isset($_POST['standard-btn'])) {
        $standard_id = $_POST['standard_id'];
        $standard_desc = $_POST['standard_desc'];
        $standard_price = $_POST['standard_price'];
        $update_query = "UPDATE standard_membership SET standard_desc = '$standard_desc', standard_price = '$standard_price' WHERE standard_id = $standard_id";

        if ($con->query($update_query) === TRUE) {
            $confirm[]= "Product updated successfully!";
            header("Location:membership_manage.php");
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
            <h1>Update Royal Membership</h1>
            <form class="product-form" action="update_membership.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="royal_id" value="<?php echo $royal_row["royal_id"]; ?>">

                <label for="royal_description">Enter Discription:</label>
                <input type="text" id="royal_description" name="royal_desc" value="<?php echo $royal_row["royal_desc"]; ?>">

                <label for="royal_price">Enter Price:</label>
                <input type="text" id="royal_price" name="royal_price" value="<?php echo $royal_row["royal_price"];?>">
                <div class="membership-upd-btn">
                    <button name="royal-btn" onclick="return //confirm('Updated Successfully..!!');">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

<div class="main-content">
        <div class="content">
            <h1>Update Classic Membership</h1>
            <form class="product-form" action="update_membership.php" method="post" enctype="multipart/form-data">
                
                <input type="hidden" name="classic_id" value="<?php echo $classic_row["classic_id"]; ?>">

                <label for="classic_description">Enter Discription:</label>
                <input type="text" id="classic_description" name="classic_desc" value="<?php echo $classic_row["classic_desc"]; ?>">

                <label for="classic_price">Enter Price:</label>
                <input type="text" id="classic_price" name="classic_price" value="<?php echo $classic_row["classic_price"];?>">
                <div class="membership-upd-btn">
                    <button name="classic-btn">Confirm Changes</button>
                </div>
               
            </form>
        </div>
</div>

<div class="main-content">
        <div class="content">
            <h1>Update Standard Membership</h1>
            <form class="product-form" action="update_membership.php" method="post" enctype="multipart/form-data">

                <input type="hidden" name="standard_id" value="<?php echo $standard_row["standard_id"]; ?>">

                <label for="standard_description">Enter Discription:</label>
                <input type="text" id="standard_description" name="standard_desc" value="<?php echo $standard_row["standard_desc"]; ?>">

                <label for="standard_price">Enter Price:</label>
                <input type="text" id="standard_price" name="standard_price" value="<?php echo $standard_row["standard_price"];?>">
                <div class="membership-upd-btn">
                    <button name="standard-btn">Confirm Changes</button>
                </div>
            </form>
        </div>
</div>
</body>
</html>