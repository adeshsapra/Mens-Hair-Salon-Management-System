<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');

$orders = "SELECT ps.*, ur.name FROM product_sales ps JOIN user_reg ur ON ps.id = ur.id";
$orders_data = mysqli_query($con,$orders);
// $order_fetch_name = mysqli_fetch_assoc($orders_data);
// $orders_name = $order_fetch_name['id'];

// $f_name_query = mysqli_query($con,"SELECT `name` FROM user_reg WHERE id = $orders_name");
// $f_name_result = mysqli_fetch_assoc($f_name_query);
// $f_name_row = $f_name_result['name'];
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
        <h1>Orders</h1>
        <p>Orders Details here.</p>
    </div>
</div>
<div class="main-content">
        <div class="content">
        <h2>Exisiting Orders</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Product Image</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr><?php
                        $id_counter = 1; 
                                while($order_fetch_row = mysqli_fetch_assoc($orders_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><?php echo $order_fetch_row["name"];; ?></td>
                                <td><img src="../upload_product_photos/<?php echo $order_fetch_row["s_img"]; ?>" alt="no Pictures" class="profile-img"></td>
                                <td><?php echo $order_fetch_row["s_name"]; ?></td>
                                <td>₹ <?php echo $order_fetch_row["s_price"]; ?></td>
                                <td><?php echo $order_fetch_row["s_size"]; ?></td>
                                <td><?php echo $order_fetch_row["s_quantity"]; ?></td>
                                <td>₹ <?php echo $order_fetch_row["s_total"]; ?></td>
                                <td>₹ <?php echo $order_fetch_row["s_grand_total"]; ?></td>
                                <td><?php echo $order_fetch_row["s_status"]; ?></td>
                                <td><?php echo $order_fetch_row["s_date"]; ?></td>
                                <td><?php echo $order_fetch_row["s_time"]; ?></td>
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