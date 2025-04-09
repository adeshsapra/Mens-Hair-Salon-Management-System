<?php

include('header.php');
// include('sidebar.php');
include('connect.php');


if(isset($_POST['royal-btn'])){
    $royal_plan = $_POST['royal_plan'];
    $royal_desc = $_POST['royal_desc'];
    $royal_price = $_POST['royal_price'];

    if(empty($royal_plan) || empty($royal_desc)){
        $message[]="Please Fill Plan And Description In Royal Pass";
    }
    else{
        $insert=mysqli_query($con,"insert into royal_membership(royal_plan,royal_desc,royal_price)
        values('$royal_plan','$royal_desc','$royal_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:membership_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$royal = "SELECT * FROM royal_membership";
$royal_data = $con->query($royal);

// classic

if(isset($_POST['classic-btn'])){
    $classic_plan = $_POST['classic_plan'];
    $classic_desc = $_POST['classic_desc'];
    $classic_price = $_POST['classic_price'];

    if(empty($classic_plan) || empty($classic_desc)){
        $message[]="Please Fill Plan And Description In Classic Pass";
    }
    else{
        $insert=mysqli_query($con,"insert into classic_membership(classic_plan,classic_desc,classic_price)
        values('$classic_plan','$classic_desc','$classic_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:membership_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$classic = "SELECT * FROM classic_membership";
$classic_data = $con->query($classic);



// Standard

if(isset($_POST['standard-btn'])){
    $standard_plan = $_POST['standard_plan'];
    $standard_desc = $_POST['standard_desc'];
    $standard_price = $_POST['standard_price'];

    if(empty($standard_plan) || empty($standard_desc)){
        $message[]="Please Fill Plan And Description In Standard Pass";
    }
    else{
        $insert=mysqli_query($con,"insert into standard_membership(standard_plan,standard_desc,standard_price)
        values('$standard_plan','$standard_desc','$standard_price')") or die('Query Failed');

        if($insert)
        {
            $confirm[]='Add Sucessfully..!';
            header("Location:membership_manage.php");
        }
        else{
            $message[]='Could Not Add Successfully..!';
        }
    }
   
}

$standard = "SELECT * FROM standard_membership";
$standard_data = $con->query($standard);

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="admin-membership-container">
        <h1>Membership Plans</h1>
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
        <div class="toggle-buttons">
            <button class="toggle-btn active" data-target="royal">Royal Pass</button>
            <button class="toggle-btn" data-target="classic">Classic Pass</button>
            <button class="toggle-btn" data-target="standard">Standard Pass</button>
        </div>

        <div class="details">
            <div id="royal" class="plan-details active">
                <h2>Royal Pass</h2>
                <p>Details about the Royal Pass. Includes premium features, exclusive offers, and more.</p>
                <div class="form-section">
                    <form action="" method="post" enctype="multipart/form-data" class="form-section">
                    <div class="plan-options">
                        <label for="royal-plan">Select Plan:</label>
                        <select id="royal-plan" class="plan-select" name="royal_plan">
                            <option value="yearly">Yearly Plan</option>
                            <option value="monthly">Monthly Plan</option>
                        </select>
                    </div>

                    <input type="text" placeholder="Enter description" class="text-field" name="royal_desc">
                    <input type="text" placeholder="Enter price" class="price-field" name="royal_price">
                    <button class="submit-btn" name="royal-btn">Submit</button>
                    </form>
                </div>
                

                <h1>Exisiting In Royal Pass</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plan</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_assoc($royal_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["royal_id"]; ?></td>
                                <td><?php echo $row["royal_plan"]; ?></td>
                                <td><?php echo $row["royal_desc"]; ?></td>
                                <td><?php echo $row["royal_price"]; ?></td>
                                <td class="membership-actions">
                                    <a href="update_membership.php?id=<?php echo $row["royal_id"]; ?>">
                                    <button class="update">Update</button>
                                </a>
                                    <a href="delete_membership.php?id=<?php echo $row["royal_id"]; ?>" onclick="return confirm('Are you sure you want to delete.?');">
                                    <button class="delete">Delete</button>
                                </a></td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
        </div>



            <div id="classic" class="plan-details">
                <h2>Classic Pass</h2>
                <p>Details about the Classic Pass. Includes standard features, discounts, and more.</p>

                <div class="form-section">
                    <form action="" method="post" enctype="multipart/form-data" class="form-section">
                    <div class="plan-options">
                        <label for="classic-plan">Select Plan:</label>
                        <select id="classic-plan" class="plan-select" name="classic_plan">
                            <option value="yearly">Yearly Plan</option>
                            <option value="monthly">Monthly Plan</option>
                        </select>
                    </div>

                    <input type="text" placeholder="Enter description" class="text-field" name="classic_desc">
                    <input type="text" placeholder="Enter price" class="price-field" name="classic_price">
                    <button class="submit-btn" name="classic-btn">Submit</button>
                    </form>
                </div>
                

                <h1>Exisiting In Royal Pass</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plan</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_assoc($classic_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["classic_id"]; ?></td>
                                <td><?php echo $row["classic_plan"]; ?></td>
                                <td><?php echo $row["classic_desc"]; ?></td>
                                <td><?php echo $row["classic_price"]; ?></td>
                                <td class="membership-actions">
                                    <a href="update_membership.php?id=<?php echo $row["classic_id"]; ?>">
                                    <button class="update">Update</button>
                                </a>
                                    <a href="delete_membership.php?id=<?php echo $row["classic_id"]; ?>"
                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                    <button class="delete">Delete</button>
                                </a></td>
                            </tr>
                            <?php
                                 }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div id="standard" class="plan-details">
                <h2>Standard Pass</h2>
                <p>Details about the Standard Pass. Basic membership with essential features.</p>

                <div class="form-section">
                    <form action="" method="post" enctype="multipart/form-data" class="form-section">
                    <div class="plan-options">
                        <label for="standard-plan">Select Plan:</label>
                        <select id="standard-plan" class="plan-select" name="standard_plan">
                            <option value="yearly">Yearly Plan</option>
                            <option value="monthly">Monthly Plan</option>
                        </select>
                    </div>

                    <input type="text" placeholder="Enter description" class="text-field" name="standard_desc">
                    <input type="text" placeholder="Enter price" class="price-field" name="standard_price">
                    <button class="submit-btn" name="standard-btn">Submit</button>
                    </form>
                </div>
                

                <h1>Exisiting In Royal Pass</h1>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plan</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                               while($row = mysqli_fetch_assoc($standard_data)){
                            ?>
                            <tr>
                                <td><?php echo $row["standard_id"]; ?></td>
                                <td><?php echo $row["standard_plan"]; ?></td>
                                <td><?php echo $row["standard_desc"]; ?></td>
                                <td><?php echo $row["standard_price"]; ?></td>
                                <td class="membership-actions">
                                    <a href="update_membership.php?id=<?php echo $row["standard_id"]; ?>">
                                    <button class="update">Update</button>
                                </a>
                                    <a href="delete_membership.php?id=<?php echo $row["standard_id"]; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                                    <button class="delete">Delete</button>
                                </a></td>
                            </tr>
                            <?php
                           }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const target = button.getAttribute('data-target');
                document.querySelectorAll('.plan-details').forEach(detail => {
                    detail.classList.remove('active');
                    if (detail.id === target) {
                        detail.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
