<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');

$user = "SELECT * FROM user_reg";
$user_data = mysqli_query($con,$user);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>customer manage</title>
    <style>
        .profile-img {
            width: 50px; 
            height: 50px; 
            object-fit: cover;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
        }
         .customer-buttons .customer-delete{
            margin-top:0.9rem;
            border-bottom: 1px solid #ddd;
         }
    </style>

</head>
<body>
    <div class="main-content">
    <div class="content">
        <h1>Customers</h1>
        <p>Manage Customers Detials here.</p>
    </div>
</div>
<div class="main-content">
        <div class="content">
        <h2>Exisiting Users</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile Image</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                        $id_counter=1;
                                while($row = mysqli_fetch_assoc($user_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><img src="../upload_img/<?php echo $row["profile_img"]; ?>" alt="no Pictures" class="profile-img"></td>
                                <td><?php echo $row["name"]; ?></td>
                                <td><?php echo $row["email"]; ?></td>
                                <td><?php echo $row["username"]; ?></td>
                                <td><?php echo $row["password"]; ?></td>
                                <td class="customer-buttons">
                                    <a href="delete_customer.php?id=<?php echo $row['id'];?>"onclick="return confirm('Are you sure you want to delete this User?');"><button class="customer-delete">Remove User
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

</body>
</html>