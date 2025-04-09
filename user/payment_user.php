<?php 
include 'connect.php';
include 'header.php'; 

$user_id = $_SESSION['user_id'];

// Query for product payments
$product_pay_fetch = mysqli_query($con, "
    SELECT product_sales.*, payment.*
    FROM product_sales
    JOIN payment ON product_sales.s_id = payment.s_id
    WHERE payment.id = '{$user_id}'");

// Query for membership payments
$membership_pay_fetch = mysqli_query($con, "
    SELECT membership_payments.*
    FROM membership_payments
    WHERE membership_payments.id = '{$user_id}'");

?>
<main class="content">
    <h1>Payment History</h1>

    <h2>Product Payments</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile No.</th>
                    <th>Method</th>
                    <th>Total Amount</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $id_counter=1;
                while($pay_fetch_row = mysqli_fetch_assoc($product_pay_fetch)){
                    $status = $pay_fetch_row["p_status"];
                    if ($status == 'Pending') {
                        $color = 'blue';
                    } elseif ($status == 'Received') {
                        $color = 'green';
                    } elseif ($status == 'Failed') {
                        $color = 'red';
                    }
                ?>
                    <tr>
                        <td><?php echo $id_counter++; ?></td>
                        <td><?php echo $pay_fetch_row["p_name"]; ?></td>
                        <td><?php echo $pay_fetch_row["p_phno"]; ?></td>
                        <td><?php echo $pay_fetch_row["p_method"]; ?></td>
                        <td>₹ <?php echo $pay_fetch_row["s_grand_total"]; ?></td>
                        <td><?php echo $pay_fetch_row["p_date"]; ?></td>
                        <td><?php echo $pay_fetch_row["p_time"]; ?></td>
                        <td style="color:<?php echo $color; ?>;"><?php echo $status; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>            
    </div>

    <h2>Membership Payments</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Membership Type</th>
                    <th>Card Name</th>
                    <th>Phone Number</th>
                    <th>Price</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $membership_counter = 1;
                while($membership_row = mysqli_fetch_assoc($membership_pay_fetch)){
                    $status = $membership_row["status"];
                    if ($status == 'Pending') {
                        $color = 'blue';
                    } elseif ($status == 'Active') {
                        $color = 'green';
                    } elseif ($status == 'Failed') {
                        $color = 'red';
                    }
                ?>
                    <tr>
                        <td><?php echo $membership_counter++; ?></td>
                        <td><?php echo $membership_row["membership_type"]; ?></td>
                        <td><?php echo $membership_row["card_name"]; ?></td>
                        <td><?php echo $membership_row["phone_number"]; ?></td>
                        <td>₹ <?php echo $membership_row["price"]; ?></td>
                        <td><?php echo $membership_row["payment_date"]; ?></td>
                        <td style="color:<?php echo $color; ?>;"><?php echo $status; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

</main>


<style>


.content {
    max-width: 1350px;
    margin: auto;
    padding: 20px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h1 {
    text-align: center;
    color: #18150d;
}

h2 {
    margin-top: 30px;
    color: #18150d;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.table-container {
    margin: 20px 0;
    overflow-x: auto; /* Enable horizontal scrolling if needed */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
    color: #555;
}

tr:hover {
    background-color: #f9f9f9; /* Highlight row on hover */
}

td {
    color: #333;
}

td[style] {
    font-weight: bold; /* Highlight status */
}

</style>
