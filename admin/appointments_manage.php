<?php 
include('header.php'); 
// include('sidebar.php');
include('connect.php');


$appointment = "SELECT * FROM appointment_history";
$appointment_data = mysqli_query($con,$appointment);

     
?>


<div class="main-content">
    <div class="content">
        <h1>Manage appointments here.</h1>

        <h2>Exisiting Appointments</h2>
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
                        <tr><?php
                        $id_counter =1;
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
                                <td class="appointment-btn">

                                    <a href="accept_appointment.php?ah_id=<?php echo $row["ah_id"]; ?>"><button class="a-update">Accept
                                    </button></a>

                                    <a href="decline_appointment.php?ah_id=<?php echo $row['ah_id'];?>"onclick="return confirm('Are you sure you want to cancelled appointment?');"><button class="a-delete">Decline
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
