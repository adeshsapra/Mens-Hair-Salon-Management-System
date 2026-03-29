<?php 
include 'connect.php';
include('header.php'); 
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Pagination Logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$count_query = "SELECT COUNT(*) AS total FROM admin";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = (int) $count_row['total'];

$offset = ($current_page - 1) * $records_per_page;

$admin = "SELECT * FROM admin ORDER BY admin_id DESC LIMIT $offset, $records_per_page";
$admin_data = mysqli_query($con, $admin);

?>

<?php
renderAdminPageIntro(
    'Admin Settings',
    'Administrator Management',
    'Control administrator accounts, update access ownership, and manage active admin users.'
);
?>

<div class="main-content">
    <div class="content">
        <div class="page-section-toolbar">
            <h2>Administrator Directory</h2>
            <a href="add_admin.php" class="add-service-btn"><i class="fas fa-plus"></i> Add New Admin</a>
        </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Password</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $id_counter = $offset + 1;
                              while($admin_row = mysqli_fetch_assoc($admin_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><?php echo $admin_row["admin_name"]; ?></td>
                                <td><?php echo $admin_row["admin_email"]; ?></td>
                                <td><?php echo $admin_row["admin_password"]; ?></td>
                                <td class="customer-buttons">
                                    <a href="delete_admin.php?id=<?php echo $admin_row['admin_id'];?>"onclick="return confirm('Are You Sure You Want To Delete This Admin?');"><button class="customer-delete">Remove Admin
                                    </button></a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    echo renderPagination($total_records, $current_page, $records_per_page, 'manage_admin.php');
                    ?>
    </div>
</div>

