<?php 
include('header.php'); 
include('sidebar.php');
include('connect.php');

$payment = "SELECT * FROM product_sales JOIN payment ON product_sales.s_id = payment.s_id";
$payment_data = mysqli_query($con, $payment);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <style>
        .customer-buttons .customer-delete {
            margin-top: 0.9rem;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="content">
            <h1>Payments</h1>
            <p>Manage Payments here.</p>
        </div>
    </div>
    <div class="main-content">
        <div class="content">
            <h2>Existing Products Payments</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone No</th>
                            <th>Method</th>
                            <th>Total Amount</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $id_counter = 1; 
                        while ($pay_fetch_row = mysqli_fetch_assoc($payment_data)) {
                        ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><?php echo $pay_fetch_row["p_name"]; ?></td>
                                <td><?php echo $pay_fetch_row["p_phno"]; ?></td>
                                <td><?php echo $pay_fetch_row["p_method"]; ?></td>
                                <td>₹ <?php echo $pay_fetch_row["s_grand_total"]; ?></td>
                                <td><?php echo $pay_fetch_row["p_date"]; ?></td>
                                <td><?php echo $pay_fetch_row["p_time"]; ?></td>
                                <td><?php echo $pay_fetch_row["p_status"]; ?></td>
                                <td class="appointment-btn">
                                    <a href="accept_payment.php?pay_id=<?php echo $pay_fetch_row["pay_id"]; ?>&action=confirm" onclick="return confirm('Are you sure you want to confirm this payment?');">
                                        <button class="a-update">Confirm</button>
                                    </a>
                                    <a href="accept_payment.php?pay_id=<?php echo $pay_fetch_row["pay_id"]; ?>&action=discard" onclick="return confirm('Are you sure you do not want to receive this payment?');">
                                        <button class="a-delete">Discard</button>
                                    </a>
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
</body>
</html>
