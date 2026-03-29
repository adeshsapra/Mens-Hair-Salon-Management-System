<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Pagination Logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$count_query = "SELECT COUNT(*) AS total FROM membership_payments";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = (int) $count_row['total'];

$offset = ($current_page - 1) * $records_per_page;

$membership = "SELECT mp.*, ur.name FROM membership_payments mp JOIN user_reg ur ON mp.id = ur.id ORDER BY mp.m_id DESC LIMIT $offset, $records_per_page";
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
<?php
renderAdminPageIntro(
    'Membership / Payment Details',
    'Membership Transactions',
    'Review subscribed memberships, payment records, and card-holder information in one table.'
);
?>
<div class="main-content">
        <div class="content">
        <h2>Membership Transaction Records</h2>
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
                        <?php 
                        $id_counter = $offset + 1;
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
                    <?php
                    echo renderPagination($total_records, $current_page, $records_per_page, 'membership_details.php');
                    ?>
        </div>
</div>
</body>
</html>
