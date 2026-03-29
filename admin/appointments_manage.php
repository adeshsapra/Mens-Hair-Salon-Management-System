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
$count_query = "SELECT COUNT(*) as total FROM appointment_history";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];

// Fetch paginated data
$appointment = "SELECT * FROM appointment_history LIMIT $offset, $records_per_page";
$appointment_data = mysqli_query($con,$appointment);

?>

<?php
renderAdminPageIntro(
    'Appointments',
    'Appointment Management',
    'Handle booking requests, review schedule details, and process appointment actions efficiently.'
);
?>

<div class="main-content">
    <div class="content">
        <h2>Appointment Records</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile No.</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id_counter = $offset + 1;
                            while($row = mysqli_fetch_assoc($appointment_data)){
                            ?>
                            <tr>
                                <td><?php echo $id_counter++; ?></td>
                                <td><?php echo $row["ah_name"]; ?></td>
                                <td><?php echo $row["ah_email"]; ?></td>
                                <td><?php echo $row["ah_no"]; ?></td>
                                <td><?php echo $row["ah_date"]; ?></td>
                                <td><?php echo $row["ah_time"]; ?></td>
                                <td><?php echo $row["ah_category"]; ?></td>
                                <td><?php echo $row["ah_type"]; ?></td>
                                <td><?php echo $row["ah_status"]; ?></td>
                                <td>
                                    <div class="action-dropdown">
                                        <button type="button" class="action-dots" onclick="toggleActionDropdown(event, <?php echo $row['ah_id']; ?>)" aria-label="Open actions" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-dropdown-content">
                                            <a href="accept_appointment.php?ah_id=<?php echo $row["ah_id"]; ?>">
                                                <i class="fas fa-check-circle"></i> Accept
                                            </a>
                                            <a href="decline_appointment.php?ah_id=<?php echo $row['ah_id'];?>" 
                                               onclick="return confirm('Are you sure you want to cancel the appointment?');" 
                                               class="delete-action">
                                                <i class="fas fa-times-circle"></i> Decline
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
                echo renderPagination($total_records, $current_page, $records_per_page, 'appointments_manage.php'); 
                ?>
    </div>
</div>
