<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Pagination Logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Count total records
$count_query = "SELECT COUNT(*) as total FROM user_reg";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];

// Fetch paginated data
$user = "SELECT * FROM user_reg LIMIT $offset, $records_per_page";
$user_data = mysqli_query($con, $user);

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
            border-radius: 50%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>

</head>
<body>
<?php
renderAdminPageIntro(
    'Clients',
    'Client Directory',
    'Review customer profiles, account details, and perform account actions with full visibility.'
);
?>
<div class="main-content">
        <div class="content">
        <h2>Customer Directory</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile Image</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Username</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id_counter = $offset + 1;
                            while($row = mysqli_fetch_assoc($user_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><img src="../upload_img/<?php echo $row["profile_img"]; ?>" alt="no Picture" class="profile-img"></td>
                                <td><?php echo $row["name"]; ?></td>
                                <td><?php echo $row["email"]; ?></td>
                                <td><?php echo $row["username"]; ?></td>
                                <td>
                                    <div class="action-dropdown">
                                        <button type="button" class="action-dots" onclick="toggleActionDropdown(event, <?php echo $row['id']; ?>)" aria-label="Open actions" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-dropdown-content">
                                            <a href="delete_customer.php?id=<?php echo $row['id'];?>" 
                                               onclick="return confirm('Are you sure you want to delete this User?');" 
                                               class="delete-action">
                                                <i class="fas fa-trash-alt"></i> Remove User
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php 
                // Display Pagination Links
                echo renderPagination($total_records, $current_page, $records_per_page, 'customer.php'); 
                ?>
        </div>
</div>

</body>
</html>
