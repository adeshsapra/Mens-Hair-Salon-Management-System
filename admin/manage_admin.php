<?php 
include 'connect.php';
include('header.php'); 

$admin = "SELECT * FROM admin";
$admin_data = mysqli_query($con,$admin);

?>

<div class="main-content">
    <div class="content">
    <h1>Admin</h1>
        <p>Manage Admin here.</p>

         <div class="right">
         <div class="product-button">
            <a href="add_admin.php">Add New Admin</a>
        </div>
</div>

        <h2 style="margin-top:3rem;">Exisiting Admin</h2>
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
                        <tr><?php
                            $id_counter=1;
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
    </div>
</div>
