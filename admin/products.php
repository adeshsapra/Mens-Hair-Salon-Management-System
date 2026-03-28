<?php

include('header.php');
// include('sidebar.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Pagination logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$count_query = "SELECT COUNT(*) AS total FROM products";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = (int) $count_row['total'];

$total_pages = max(1, (int) ceil($total_records / $records_per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $records_per_page;

$query = "SELECT * FROM products ORDER BY p_id DESC LIMIT $offset, $records_per_page";
$all_product = $con->query($query);
$has_products = mysqli_num_rows($all_product) > 0;
?>

<?php
renderAdminPageIntro(
    'Products',
    'Product Management',
    'Manage catalog items, pricing, stock levels, and product details for the salon store.'
);
?>

<div class="main-content">
    <div class="content">
        <div class="page-section-toolbar">
            <h2>Product Catalog</h2>
            <a href="add_product.php" class="add-service-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <!-- Product List -->
        <div class="product-list">
            <?php if ($has_products): ?>
            <?php while($row = mysqli_fetch_assoc($all_product)){ ?>
                    <?php
                        $original_price = (float) $row["p_price"];
                        $discount = isset($row["p_discount"]) ? (float) $row["p_discount"] : 0;
                        $discounted_price = $original_price - (($original_price * $discount) / 100);
                    ?>
                    <div class="product-item">
                        <img src="../upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product Image">
                        <div class="product-info">
                            <h2><?php echo $row["p_name"]; ?></h2>
                            <p><?php echo $row["p_desc"]; ?></p>
                            <p>
                                <?php if ($discount > 0): ?>
                                    <span style="text-decoration: line-through; color: #777;">₹ <?php echo number_format($original_price, 2); ?></span>
                                    <span style="color: var(--brand); font-weight: 700;">₹ <?php echo number_format($discounted_price, 2); ?></span>
                                    <small style="margin-left: 6px; color: #1e8e3e;"><?php echo number_format($discount, 0); ?>% OFF</small>
                                <?php else: ?>
                                    ₹ <?php echo number_format($original_price, 2); ?>
                                <?php endif; ?>
                                <i> ( <?php echo $row["p_size"]; ?> )</i>
                            </p>
                            <p>Quantity: <?php echo $row["p_quantity"]; ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="update_product.php?id=<?php echo $row["p_id"]; ?>">
                                <button class="update">Edit</button>
                            </a>
                            <a href="delete_product.php?id=<?php echo $row["p_id"]; ?>">
                                <button class="delete">Delete</button>
                            </a>
                        </div>
                    </div>
            <?php } ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>

            <?php
            echo renderPagination($total_records, $current_page, $records_per_page, 'products.php');
            ?>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
        container.style.display = 'none';
    }
}
</script>

