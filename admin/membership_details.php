<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');

$membership = "SELECT mp.*, ur.name FROM membership_payments mp JOIN user_reg ur ON mp.id = ur.id";
$membership_data = mysqli_query($con,$membership);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment manage</title>
    <style>
        .profile-img {
            width: 30%;
            object-fit: cover;
        }
        table{
            text-align:center;
        }
    </style>

</head>
<body>
    <div class="main-content">
    <div class="content">
        <h1>Membership</h1>
        <p>Memberships Details here.</p>
    </div>
</div>
<div class="main-content">
        <div class="content">
        <h2>Exisiting Membership Details</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Membership Type</th>
                                <th>Price</th>
                                <th>Card Holder Name</th>
                                <th>Phone No</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php 
                        $id_counter =1;
                                while($membership_fetch_row = mysqli_fetch_assoc($membership_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><?php echo $membership_fetch_row["name"]; ?></td>
                                <td><?php echo $membership_fetch_row["membership_type"]; ?></td>
                                <td>₹ <?php echo $membership_fetch_row["price"]; ?></td>
                                <td><?php echo $membership_fetch_row["card_name"]; ?></td>
                                <td><?php echo $membership_fetch_row["phone_number"]; ?></td>
                                <td><?php echo $membership_fetch_row["payment_date"]; ?></td>
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