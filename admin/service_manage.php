<?php

include('header.php');
// include('sidebar.php'); 
include('connect.php');

// Hair cut

if(isset($_POST['haircut-btn'])){
    $haircut_category = $_POST['haircut_category'];
    $haircut_service = $_POST['haircut_service'];
    $haircut_price = $_POST['haircut_price'];

    if(empty($haircut_category) || empty($haircut_service) || empty($haircut_price)){
        $message[]="Please Fill All Details..!!";
    }
    else{
        $insert=mysqli_query($con,"insert into haircut_service(hair_category,hair_service,hair_price)
        values('$haircut_category','$haircut_service','$haircut_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:service_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$haircut = "SELECT * FROM haircut_service";
$haircut_data = $con->query($haircut);




// beard trim

if(isset($_POST['beard-btn'])){
    $beard_service = $_POST['beard_service'];
    $beard_price = $_POST['beard_price'];

    if(empty($beard_service) || empty($beard_price)){
        $message[]="Please Fill All Details..!!";
    }
    else{
        $insert=mysqli_query($con,"insert into beard_service(beard_service,beard_price)
        values('$beard_service','$beard_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:service_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$beard = "SELECT * FROM beard_service";
$beard_data = $con->query($beard);


// skin treatment

if(isset($_POST['skin-btn'])){
    $skin_service = $_POST['skin_service'];
    $skin_price = $_POST['skin_price'];

    if(empty($skin_service) || empty($skin_price)){
        $message[]="Please Fill All Details..!!";
    }
    else{
        $insert=mysqli_query($con,"insert into skin_service(skin_service,skin_price)
        values('$skin_service','$skin_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:service_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$skin = "SELECT * FROM skin_service";
$skin_data = $con->query($skin);



// Hair cut

if(isset($_POST['spa-btn'])){
    $spa_category = $_POST['spa_category'];
    $spa_service = $_POST['spa_service'];
    $spa_price = $_POST['spa_price'];

    if(empty($spa_category) || empty($spa_service) || empty($spa_price)){
        $message[]="Please Fill All Details..!!";
    }
    else{
        $insert=mysqli_query($con,"insert into spa_service(spa_category,spa_service,spa_price)
        values('$spa_category','$spa_service','$spa_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:service_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$spa = "SELECT * FROM spa_service";
$spa_data = $con->query($spa);



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>service manage</title>
</head>
<body>
<div class="service-body">
    <div class="main-service-container">
        <div class="main-heading">
            <h1>Services</h1>
            <p>Manage Services Here.</p>
        </div>
        <div class="service-grid">

        <!-- hair cut -->
            <div class="service-container">
                <div class="service-category">
                    <h3>HairCut Services</h3>

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
                    <form action="" method="post" enctype="multipart/form-data">
                        <label for="haircut-category">Select Main Category:</label>
                        <select id="haircut-category" name="haircut_category">
                            <option value="hairstyle">Hair Style</option>
                            <option value="hairdesign">Hair Design</option>
                        </select>

                        <div class="form-group">
                            <label for="haircut-sub-service-name">Sub-Service Name:</label>
                            <input type="text" id="haircut-sub-service-name" placeholder="Enter sub-service name" name="haircut_service">
                        </div>
                        <div class="form-group">
                            <label for="haircut-sub-service-price">Price:</label>
                            <input type="text" id="haircut-sub-service-price" placeholder="Enter price" name="haircut_price">
                        </div>
                        <button type="submit" name="haircut-btn">Add Sub-Service</button>
                    </form>

                <h1>Exisiting Service</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                                while($row = mysqli_fetch_assoc($haircut_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["hair_id"]; ?></td>
                                <td><?php echo $row["hair_category"]; ?></td>
                                <td><?php echo $row["hair_service"]; ?></td>
                                <td><?php echo $row["hair_price"]; ?></td>
                                <td class="services-buttons">

                                    <a href="update_service.php?id=<?php echo $row["hair_id"]; ?>"><button class="service-update"><i class="fas fa-edit"></i>
                                    </button></a>

                                    <a href="delete_service.php?id=<?php echo $row['hair_id'];?>"onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i>
                                    </button></a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                </div>
            </div>

            <!-- hair cut -->

            
            <!-- beard trim -->
            <div class="service-container">
                <div class="service-category">
                    <h3>Beard Trim Services</h3>
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
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="beard-sub-service-name">Sub-Service Name:</label>
                            <input type="text" id="beard-sub-service-name" placeholder="Enter sub-service name" name="beard_service">
                        </div>
                        <div class="form-group">
                            <label for="beard-sub-service-price">Price:</label>
                            <input type="text" id="beard-sub-service-price" placeholder="Enter price" name="beard_price">
                        </div>
                        <button type="submit" name="beard-btn" class="admin-service-btn">Add Sub-Service</button>
                    </form>

                    <h1>Exisiting Service</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                                while($row = mysqli_fetch_assoc($beard_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["beard_id"]; ?></td>
                                <td><?php echo $row["beard_service"]; ?></td>
                                <td><?php echo $row["beard_price"]; ?></td>
                                <td class="services-buttons">
                                    
                                <a href="update_service.php?id=<?php echo $row["beard_id"]; ?>"><button class="service-update"><i class="fas fa-edit"></i>
                                    </button></a>

                                    <a href="delete_service.php?id=<?php echo $row['beard_id'];?>"onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i>
                                    </button></a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                </div>
            </div>

            <!-- beard trim -->

            <!-- skin treatment -->
            <div class="service-container">
                <div class="service-category">
                    <h3>Skin Treatment Services</h3>
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
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="skin-sub-service-name">Sub-Service Name:</label>
                            <input type="text" id="skin-sub-service-name" placeholder="Enter sub-service name" name="skin_service">
                        </div>
                        <div class="form-group">
                            <label for="skin-sub-service-price">Price:</label>
                            <input type="text" id="skin-sub-service-price" placeholder="Enter price" name="skin_price">
                        </div>
                        <button type="submit" name="skin-btn" class="admin-service-btn">Add Sub-Service</button>
                    </form>

                    <h1>Exisiting Service</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                                while($row = mysqli_fetch_assoc($skin_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["skin_id"]; ?></td>
                                <td><?php echo $row["skin_service"]; ?></td>
                                <td><?php echo $row["skin_price"]; ?></td>
                                <td class="services-buttons">
                                   
                                <a href="update_service.php?id=<?php echo $row["skin_id"]; ?>"><button class="service-update"><i class="fas fa-edit"></i>
                                    </button></a>

                                    <a href="delete_service.php?id=<?php echo $row['skin_id'];?>"onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i>
                                    </button></a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                </div>
            </div>

            <!-- skin treatment -->



            <!-- spa services -->

            <div class="service-container">
                <div class="service-category">
                    <h3>Spa Services</h3>
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
                    <form action="" method="post" enctype="multipart/form-data">
                        <label for="spa-category">Select Main Category:</label>
                        <select id="spa-category" name="spa_category">
                            <option value="bodytreatment">Body Treatment</option>
                            <option value="bodymassage">Body Massage</option>
                        </select>
                        <div class="form-group">
                            <label for="spa-sub-service-name">Sub-Service Name:</label>
                            <input type="text" id="spa-sub-service-name" placeholder="Enter sub-service name" name="spa_service">
                        </div>
                        <div class="form-group">
                            <label for="spa-sub-service-price">Price:</label>
                            <input type="text" id="spa-sub-service-price" placeholder="Enter price" name="spa_price">
                        </div>
                        <button type="submit" name="spa-btn">Add Sub-Service</button>
                    </form>

                    <h1>Exisiting Service</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                                while($row = mysqli_fetch_assoc($spa_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["spa_id"]; ?></td>
                                <td><?php echo $row["spa_category"]; ?></td>
                                <td><?php echo $row["spa_service"]; ?></td>
                                <td><?php echo $row["spa_price"]; ?></td>
                                <td class="services-buttons">
                                    
                                <a href="update_service.php?id=<?php echo $row["spa_id"]; ?>"><button class="service-update"><i class="fas fa-edit"></i>
                                    </button></a>

                                    <a href="delete_service.php?id=<?php echo $row['spa_id'];?>"onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i>
                                    </button></a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>


                </div>
            </div>

            <!-- spa services -->

        </div>
    </div>
</div>

</body>
</html>
