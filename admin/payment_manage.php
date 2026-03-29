<?php 
include('header.php'); 
include('sidebar.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Pagination Logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$count_query = "SELECT COUNT(*) AS total FROM product_sales JOIN payment ON product_sales.s_id = payment.s_id";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = (int) $count_row['total'];

$offset = ($current_page - 1) * $records_per_page;

$payment = "SELECT * FROM product_sales JOIN payment ON product_sales.s_id = payment.s_id ORDER BY product_sales.s_id DESC LIMIT $offset, $records_per_page";
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
    <?php
    renderAdminPageIntro(
        'Payments',
        'Payment Management',
        'Audit order payments, verify settlement status, and review transaction-specific details.'
    );
    ?>
    <div class="main-content">
        <div class="content">
            <h2>Payment Transactions</h2>
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
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $id_counter = $offset + 1; 
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
                                <td>
                                    <?php 
                                    if ($pay_fetch_row["p_method"] == 'Stripe' || $pay_fetch_row["p_method"] == 'stripe') {
                                        echo '<span style="font-size: 11px; color: #666;">ID: ' . $pay_fetch_row["stripe_payment_intent_id"] . '</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                echo renderPagination($total_records, $current_page, $records_per_page, 'payment_manage.php');
                ?>
            </div>
        </div>
    </div>
</body>
</html>
