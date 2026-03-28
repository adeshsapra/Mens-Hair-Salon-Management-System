<?php

include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Messages array
$messages = [];
$confirms = [];
$records_per_page = 10;
$valid_tabs = ['haircut', 'beard', 'skin', 'spa'];
$active_tab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'haircut';
if (!in_array($active_tab, $valid_tabs, true)) {
    $active_tab = 'haircut';
}

// ============================================
// ADD SERVICES LOGIC
// ============================================

// Hair cut
if (isset($_POST['haircut-btn'])) {
    $active_tab = 'haircut';
    $haircut_category = $_POST['haircut_category'];
    $haircut_service = $_POST['haircut_service'];
    $haircut_price = $_POST['haircut_price'];

    if (empty($haircut_category) || empty($haircut_service) || empty($haircut_price)) {
        $messages[] = "Please Fill All Details..!!";
    }
    else {
        $insert = mysqli_query($con, "insert into haircut_service(hair_category,hair_service,hair_price)
        values('$haircut_category','$haircut_service','$haircut_price')") or die('Query Failed');

        if ($insert)
            $confirms[] = 'Sub-Service Added Successfully..!';
        else
            $messages[] = 'Could Not Add Successfully..!';
    }
}

// beard trim
if (isset($_POST['beard-btn'])) {
    $active_tab = 'beard';
    $beard_service = $_POST['beard_service'];
    $beard_price = $_POST['beard_price'];

    if (empty($beard_service) || empty($beard_price)) {
        $messages[] = "Please Fill All Details..!!";
    }
    else {
        $insert = mysqli_query($con, "insert into beard_service(beard_service,beard_price)
        values('$beard_service','$beard_price')") or die('Query Failed');

        if ($insert)
            $confirms[] = 'Sub-Service Added Successfully..!';
        else
            $messages[] = 'Could Not Add Successfully..!';
    }
}

// skin treatment
if (isset($_POST['skin-btn'])) {
    $active_tab = 'skin';
    $skin_service = $_POST['skin_service'];
    $skin_price = $_POST['skin_price'];

    if (empty($skin_service) || empty($skin_price)) {
        $messages[] = "Please Fill All Details..!!";
    }
    else {
        $insert = mysqli_query($con, "insert into skin_service(skin_service,skin_price)
        values('$skin_service','$skin_price')") or die('Query Failed');

        if ($insert)
            $confirms[] = 'Sub-Service Added Successfully..!';
        else
            $messages[] = 'Could Not Add Successfully..!';
    }
}

// spa
if (isset($_POST['spa-btn'])) {
    $active_tab = 'spa';
    $spa_category = $_POST['spa_category'];
    $spa_service = $_POST['spa_service'];
    $spa_price = $_POST['spa_price'];

    if (empty($spa_category) || empty($spa_service) || empty($spa_price)) {
        $messages[] = "Please Fill All Details..!!";
    }
    else {
        $insert = mysqli_query($con, "insert into spa_service(spa_category,spa_service,spa_price)
        values('$spa_category','$spa_service','$spa_price')") or die('Query Failed');

        if ($insert)
            $confirms[] = 'Sub-Service Added Successfully..!';
        else
            $messages[] = 'Could Not Add Successfully..!';
    }
}


// ============================================
// UPDATE SERVICES LOGIC
// ============================================

// Hair cut
if (isset($_POST['update-haircut-btn'])) {
    $active_tab = 'haircut';
    $id = $_POST['update_haircut_id'];
    $category = $_POST['update_haircut_category'];
    $service = $_POST['update_haircut_service'];
    $price = $_POST['update_haircut_price'];

    if (!empty($id) && !empty($category) && !empty($service) && !empty($price)) {
        $update = mysqli_query($con, "UPDATE haircut_service SET hair_category='$category', hair_service='$service', hair_price='$price' WHERE hair_id='$id'");
        if ($update)
            $confirms[] = 'Sub-Service Updated Successfully..!';
        else
            $messages[] = 'Could Not Update Successfully..!';
    }
}

// Beard trim
if (isset($_POST['update-beard-btn'])) {
    $active_tab = 'beard';
    $id = $_POST['update_beard_id'];
    $service = $_POST['update_beard_service'];
    $price = $_POST['update_beard_price'];

    if (!empty($id) && !empty($service) && !empty($price)) {
        $update = mysqli_query($con, "UPDATE beard_service SET beard_service='$service', beard_price='$price' WHERE beard_id='$id'");
        if ($update)
            $confirms[] = 'Sub-Service Updated Successfully..!';
        else
            $messages[] = 'Could Not Update Successfully..!';
    }
}

// Skin treatment
if (isset($_POST['update-skin-btn'])) {
    $active_tab = 'skin';
    $id = $_POST['update_skin_id'];
    $service = $_POST['update_skin_service'];
    $price = $_POST['update_skin_price'];

    if (!empty($id) && !empty($service) && !empty($price)) {
        $update = mysqli_query($con, "UPDATE skin_service SET skin_service='$service', skin_price='$price' WHERE skin_id='$id'");
        if ($update)
            $confirms[] = 'Sub-Service Updated Successfully..!';
        else
            $messages[] = 'Could Not Update Successfully..!';
    }
}

// Spa
if (isset($_POST['update-spa-btn'])) {
    $active_tab = 'spa';
    $id = $_POST['update_spa_id'];
    $category = $_POST['update_spa_category'];
    $service = $_POST['update_spa_service'];
    $price = $_POST['update_spa_price'];

    if (!empty($id) && !empty($category) && !empty($service) && !empty($price)) {
        $update = mysqli_query($con, "UPDATE spa_service SET spa_category='$category', spa_service='$service', spa_price='$price' WHERE spa_id='$id'");
        if ($update)
            $confirms[] = 'Sub-Service Updated Successfully..!';
        else
            $messages[] = 'Could Not Update Successfully..!';
    }
}


// Fetch data with pagination for each tab
$haircut_page = isset($_GET['haircut_page']) ? (int) $_GET['haircut_page'] : 1;
$beard_page = isset($_GET['beard_page']) ? (int) $_GET['beard_page'] : 1;
$skin_page = isset($_GET['skin_page']) ? (int) $_GET['skin_page'] : 1;
$spa_page = isset($_GET['spa_page']) ? (int) $_GET['spa_page'] : 1;

if ($haircut_page < 1) $haircut_page = 1;
if ($beard_page < 1) $beard_page = 1;
if ($skin_page < 1) $skin_page = 1;
if ($spa_page < 1) $spa_page = 1;

$haircut_total_result = mysqli_query($con, "SELECT COUNT(*) AS total FROM haircut_service");
$haircut_total_records = (int) mysqli_fetch_assoc($haircut_total_result)['total'];
$haircut_total_pages = max(1, (int) ceil($haircut_total_records / $records_per_page));
if ($haircut_page > $haircut_total_pages) $haircut_page = $haircut_total_pages;
$haircut_offset = ($haircut_page - 1) * $records_per_page;
$haircut_data = mysqli_query($con, "SELECT * FROM haircut_service ORDER BY hair_id DESC LIMIT $haircut_offset, $records_per_page");

$beard_total_result = mysqli_query($con, "SELECT COUNT(*) AS total FROM beard_service");
$beard_total_records = (int) mysqli_fetch_assoc($beard_total_result)['total'];
$beard_total_pages = max(1, (int) ceil($beard_total_records / $records_per_page));
if ($beard_page > $beard_total_pages) $beard_page = $beard_total_pages;
$beard_offset = ($beard_page - 1) * $records_per_page;
$beard_data = mysqli_query($con, "SELECT * FROM beard_service ORDER BY beard_id DESC LIMIT $beard_offset, $records_per_page");

$skin_total_result = mysqli_query($con, "SELECT COUNT(*) AS total FROM skin_service");
$skin_total_records = (int) mysqli_fetch_assoc($skin_total_result)['total'];
$skin_total_pages = max(1, (int) ceil($skin_total_records / $records_per_page));
if ($skin_page > $skin_total_pages) $skin_page = $skin_total_pages;
$skin_offset = ($skin_page - 1) * $records_per_page;
$skin_data = mysqli_query($con, "SELECT * FROM skin_service ORDER BY skin_id DESC LIMIT $skin_offset, $records_per_page");

$spa_total_result = mysqli_query($con, "SELECT COUNT(*) AS total FROM spa_service");
$spa_total_records = (int) mysqli_fetch_assoc($spa_total_result)['total'];
$spa_total_pages = max(1, (int) ceil($spa_total_records / $records_per_page));
if ($spa_page > $spa_total_pages) $spa_page = $spa_total_pages;
$spa_offset = ($spa_page - 1) * $records_per_page;
$spa_data = mysqli_query($con, "SELECT * FROM spa_service ORDER BY spa_id DESC LIMIT $spa_offset, $records_per_page");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
</head>
<body>
<?php
renderAdminPageIntro(
    'Services',
    'Service Catalog Management',
    'Maintain all service categories, pricing, and sub-service details with tab-based operational controls.'
);
?>

<div class="service-body">
    <div class="main-service-container">
        <?php
foreach ($messages as $msg) {
    echo '<div class="message">' . $msg . '</div>';
}
foreach ($confirms as $conf) {
    echo '<div class="confirm">' . $conf . '</div>';
}

// Backward compatibility for generic variables
if (isset($message) && is_array($message)) {
    foreach ($message as $m)
        echo '<div class="message">' . $m . '</div>';
}
if (isset($confirm) && is_array($confirm)) {
    foreach ($confirm as $c)
        echo '<div class="confirm">' . $c . '</div>';
}
?>

        <div class="tabs-container">
            <div class="tab-header">
                <button type="button" class="tab-btn <?php echo $active_tab === 'haircut' ? 'active' : ''; ?>" onclick="openTab(event, 'haircut')"><i class="fas fa-cut"></i> HairCut Services</button>
                <button type="button" class="tab-btn <?php echo $active_tab === 'beard' ? 'active' : ''; ?>" onclick="openTab(event, 'beard')"><i class="fas fa-smile"></i> Beard Trim Services</button>
                <button type="button" class="tab-btn <?php echo $active_tab === 'skin' ? 'active' : ''; ?>" onclick="openTab(event, 'skin')"><i class="fas fa-spa"></i> Skin Treatment Services</button>
                <button type="button" class="tab-btn <?php echo $active_tab === 'spa' ? 'active' : ''; ?>" onclick="openTab(event, 'spa')"><i class="fas fa-leaf"></i> Spa Services</button>
            </div>

            <!-- Haircut Tab -->
            <div id="haircut" class="tab-content <?php echo $active_tab === 'haircut' ? 'active' : ''; ?>">
                <div class="tab-top">
                    <h3>HairCut Services</h3>
                    <button class="add-service-btn" onclick="openModal('modal_haircut')"><i class="fas fa-plus"></i> Add Sub-Service</button>
                </div>
                <div class="service-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$h_num = $haircut_offset + 1;
while ($row = mysqli_fetch_assoc($haircut_data)) { ?>
                            <tr>
                                <td><?php echo $h_num++; ?></td>
                                <td><?php echo $row["hair_category"]; ?></td>
                                <td><?php echo $row["hair_service"]; ?></td>
                                <td>₹<?php echo $row["hair_price"]; ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button class="service-update" onclick="openEditModal('haircut', <?php echo $row['hair_id']; ?>, '<?php echo addslashes($row['hair_service']); ?>', <?php echo $row['hair_price']; ?>, '<?php echo addslashes($row['hair_category']); ?>')"><i class="fas fa-edit"></i></button>
                                        <a href="delete_service.php?id=<?php echo $row['hair_id']; ?>" onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i></button></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
}?>
                        </tbody>
                    </table>
                </div>
                <?php
                echo renderPagination(
                    $haircut_total_records,
                    $haircut_page,
                    $records_per_page,
                    'service_manage.php',
                    [
                        'tab' => 'haircut',
                        'beard_page' => $beard_page,
                        'skin_page' => $skin_page,
                        'spa_page' => $spa_page
                    ],
                    'haircut_page'
                );
                ?>
            </div>

            <!-- Beard Trim Tab -->
            <div id="beard" class="tab-content <?php echo $active_tab === 'beard' ? 'active' : ''; ?>">
                <div class="tab-top">
                    <h3>Beard Trim Services</h3>
                    <button class="add-service-btn" onclick="openModal('modal_beard')"><i class="fas fa-plus"></i> Add Sub-Service</button>
                </div>
                <div class="service-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$b_num = $beard_offset + 1;
while ($row = mysqli_fetch_assoc($beard_data)) { ?>
                            <tr>
                                <td><?php echo $b_num++; ?></td>
                                <td><?php echo $row["beard_service"]; ?></td>
                                <td>₹<?php echo $row["beard_price"]; ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button class="service-update" onclick="openEditModal('beard', <?php echo $row['beard_id']; ?>, '<?php echo addslashes($row['beard_service']); ?>', <?php echo $row['beard_price']; ?>)"><i class="fas fa-edit"></i></button>
                                        <a href="delete_service.php?id=<?php echo $row['beard_id']; ?>" onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i></button></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
}?>
                        </tbody>
                    </table>
                </div>
                <?php
                echo renderPagination(
                    $beard_total_records,
                    $beard_page,
                    $records_per_page,
                    'service_manage.php',
                    [
                        'tab' => 'beard',
                        'haircut_page' => $haircut_page,
                        'skin_page' => $skin_page,
                        'spa_page' => $spa_page
                    ],
                    'beard_page'
                );
                ?>
            </div>

            <!-- Skin Treatment Tab -->
            <div id="skin" class="tab-content <?php echo $active_tab === 'skin' ? 'active' : ''; ?>">
                <div class="tab-top">
                    <h3>Skin Treatment Services</h3>
                    <button class="add-service-btn" onclick="openModal('modal_skin')"><i class="fas fa-plus"></i> Add Sub-Service</button>
                </div>
                <div class="service-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$s_num = $skin_offset + 1;
while ($row = mysqli_fetch_assoc($skin_data)) { ?>
                            <tr>
                                <td><?php echo $s_num++; ?></td>
                                <td><?php echo $row["skin_service"]; ?></td>
                                <td>₹<?php echo $row["skin_price"]; ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button class="service-update" onclick="openEditModal('skin', <?php echo $row['skin_id']; ?>, '<?php echo addslashes($row['skin_service']); ?>', <?php echo $row['skin_price']; ?>)"><i class="fas fa-edit"></i></button>
                                        <a href="delete_service.php?id=<?php echo $row['skin_id']; ?>" onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i></button></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
}?>
                        </tbody>
                    </table>
                </div>
                <?php
                echo renderPagination(
                    $skin_total_records,
                    $skin_page,
                    $records_per_page,
                    'service_manage.php',
                    [
                        'tab' => 'skin',
                        'haircut_page' => $haircut_page,
                        'beard_page' => $beard_page,
                        'spa_page' => $spa_page
                    ],
                    'skin_page'
                );
                ?>
            </div>

            <!-- Spa Tab -->
            <div id="spa" class="tab-content <?php echo $active_tab === 'spa' ? 'active' : ''; ?>">
                <div class="tab-top">
                    <h3>Spa Services</h3>
                    <button class="add-service-btn" onclick="openModal('modal_spa')"><i class="fas fa-plus"></i> Add Sub-Service</button>
                </div>
                <div class="service-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Service-Name</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$spa_num = $spa_offset + 1;
while ($row = mysqli_fetch_assoc($spa_data)) { ?>
                            <tr>
                                <td><?php echo $spa_num++; ?></td>
                                <td><?php echo $row["spa_category"]; ?></td>
                                <td><?php echo $row["spa_service"]; ?></td>
                                <td>₹<?php echo $row["spa_price"]; ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button class="service-update" onclick="openEditModal('spa', <?php echo $row['spa_id']; ?>, '<?php echo addslashes($row['spa_service']); ?>', <?php echo $row['spa_price']; ?>, '<?php echo addslashes($row['spa_category']); ?>')"><i class="fas fa-edit"></i></button>
                                        <a href="delete_service.php?id=<?php echo $row['spa_id']; ?>" onclick="return confirm('Are you sure you want to delete this Service?');"><button class="service-delete"><i class="fas fa-trash"></i></button></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
}?>
                        </tbody>
                    </table>
                </div>
                <?php
                echo renderPagination(
                    $spa_total_records,
                    $spa_page,
                    $records_per_page,
                    'service_manage.php',
                    [
                        'tab' => 'spa',
                        'haircut_page' => $haircut_page,
                        'beard_page' => $beard_page,
                        'skin_page' => $skin_page
                    ],
                    'spa_page'
                );
                ?>
            </div>

        </div>
    </div>
</div>

<!-- ======================= -->
<!-- MODALS FOR ADDING       -->
<!-- ======================= -->

<!-- Haircut Modal -->
<div class="modal-overlay" id="modal_haircut">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add HairCut Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_haircut')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <label>Select Main Category:</label>
            <select name="haircut_category" required>
                <option value="hairstyle" selected>Hair Style</option>
                <option value="hairdesign">Hair Design</option>
            </select>
            <label>Sub-Service Name:</label>
            <input type="text" name="haircut_service" placeholder="Enter sub-service name" required>
            <label>Price:</label>
            <input type="number" name="haircut_price" placeholder="Enter price" required>
            <button type="submit" name="haircut-btn" class="modal-submit-btn">Add Sub-Service</button>
        </form>
    </div>
</div>

<!-- Beard Trim Modal -->
<div class="modal-overlay" id="modal_beard">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Beard Trim Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_beard')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <label>Sub-Service Name:</label>
            <input type="text" name="beard_service" placeholder="Enter sub-service name" required>
            <label>Price:</label>
            <input type="number" name="beard_price" placeholder="Enter price" required>
            <button type="submit" name="beard-btn" class="modal-submit-btn">Add Sub-Service</button>
        </form>
    </div>
</div>

<!-- Skin Treatment Modal -->
<div class="modal-overlay" id="modal_skin">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Skin Treatment Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_skin')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <label>Sub-Service Name:</label>
            <input type="text" name="skin_service" placeholder="Enter sub-service name" required>
            <label>Price:</label>
            <input type="number" name="skin_price" placeholder="Enter price" required>
            <button type="submit" name="skin-btn" class="modal-submit-btn">Add Sub-Service</button>
        </form>
    </div>
</div>

<!-- Spa Modal -->
<div class="modal-overlay" id="modal_spa">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Spa Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_spa')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <label>Select Main Category:</label>
            <select name="spa_category" required>
                <option value="bodytreatment" selected>Body Treatment</option>
                <option value="bodymassage">Body Massage</option>
            </select>
            <label>Sub-Service Name:</label>
            <input type="text" name="spa_service" placeholder="Enter sub-service name" required>
            <label>Price:</label>
            <input type="number" name="spa_price" placeholder="Enter price" required>
            <button type="submit" name="spa-btn" class="modal-submit-btn">Add Sub-Service</button>
        </form>
    </div>
</div>


<!-- ======================= -->
<!-- MODALS FOR EDITING      -->
<!-- ======================= -->

<!-- Edit Haircut Modal -->
<div class="modal-overlay" id="modal_edit_haircut">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit HairCut Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_edit_haircut')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <input type="hidden" name="update_haircut_id" id="edit_haircut_id">
            <label>Select Main Category:</label>
            <select name="update_haircut_category" id="edit_haircut_category" required>
                <option value="hairstyle">Hair Style</option>
                <option value="hairdesign">Hair Design</option>
            </select>
            <label>Sub-Service Name:</label>
            <input type="text" name="update_haircut_service" id="edit_haircut_service" required>
            <label>Price:</label>
            <input type="number" name="update_haircut_price" id="edit_haircut_price" required>
            <button type="submit" name="update-haircut-btn" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Save Changes</button>
        </form>
    </div>
</div>

<!-- Edit Beard Trim Modal -->
<div class="modal-overlay" id="modal_edit_beard">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Beard Trim Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_edit_beard')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <input type="hidden" name="update_beard_id" id="edit_beard_id">
            <label>Sub-Service Name:</label>
            <input type="text" name="update_beard_service" id="edit_beard_service" required>
            <label>Price:</label>
            <input type="number" name="update_beard_price" id="edit_beard_price" required>
            <button type="submit" name="update-beard-btn" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Save Changes</button>
        </form>
    </div>
</div>

<!-- Edit Skin Treatment Modal -->
<div class="modal-overlay" id="modal_edit_skin">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Skin Treatment Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_edit_skin')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <input type="hidden" name="update_skin_id" id="edit_skin_id">
            <label>Sub-Service Name:</label>
            <input type="text" name="update_skin_service" id="edit_skin_service" required>
            <label>Price:</label>
            <input type="number" name="update_skin_price" id="edit_skin_price" required>
            <button type="submit" name="update-skin-btn" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Save Changes</button>
        </form>
    </div>
</div>

<!-- Edit Spa Modal -->
<div class="modal-overlay" id="modal_edit_spa">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Spa Sub-Service</h3>
            <button class="close-modal" onclick="closeModal('modal_edit_spa')">&times;</button>
        </div>
        <form class="modal-form" action="" method="post">
            <input type="hidden" name="update_spa_id" id="edit_spa_id">
            <label>Select Main Category:</label>
            <select name="update_spa_category" id="edit_spa_category" required>
                <option value="bodytreatment">Body Treatment</option>
                <option value="bodymassage">Body Massage</option>
            </select>
            <label>Sub-Service Name:</label>
            <input type="text" name="update_spa_service" id="edit_spa_service" required>
            <label>Price:</label>
            <input type="number" name="update_spa_price" id="edit_spa_price" required>
            <button type="submit" name="update-spa-btn" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    if(evt) evt.currentTarget.classList.add("active");
}

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Function to populate and display the edit modals perfectly
function openEditModal(type, id, service, price, category = null) {
    if (type === 'haircut') {
        document.getElementById('edit_haircut_id').value = id;
        document.getElementById('edit_haircut_category').value = category;
        document.getElementById('edit_haircut_service').value = service;
        document.getElementById('edit_haircut_price').value = price;
        openModal('modal_edit_haircut');
    } else if (type === 'beard') {
        document.getElementById('edit_beard_id').value = id;
        document.getElementById('edit_beard_service').value = service;
        document.getElementById('edit_beard_price').value = price;
        openModal('modal_edit_beard');
    } else if (type === 'skin') {
        document.getElementById('edit_skin_id').value = id;
        document.getElementById('edit_skin_service').value = service;
        document.getElementById('edit_skin_price').value = price;
        openModal('modal_edit_skin');
    } else if (type === 'spa') {
        document.getElementById('edit_spa_id').value = id;
        document.getElementById('edit_spa_category').value = category;
        document.getElementById('edit_spa_service').value = service;
        document.getElementById('edit_spa_price').value = price;
        openModal('modal_edit_spa');
    }
}

// Close modal when clicking outside of modal box
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
    }
}

// Prevent form resubmission warning
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

document.addEventListener('DOMContentLoaded', function() {
    openTab(null, <?php echo json_encode($active_tab); ?>);
});

</script>
</body>
</html>
