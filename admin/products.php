<?php

include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');
require_once('filter_helper.php');

function buildProductsRedirectUrl(string $type, string $message, bool $openCategories, int $page): string
{
    $params = [
        'toast' => $type,
        'msg' => $message,
    ];
    if ($openCategories) {
        $params['open'] = 'categories';
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return 'products.php?' . http_build_query($params);
}

$requested_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($requested_page < 1) {
    $requested_page = 1;
}

$category_messages = [];
$open_categories_modal = isset($_GET['open']) && $_GET['open'] === 'categories';

$category_table_ready = false;
$category_column_ready = false;

$category_table_check = mysqli_query($con, "SHOW TABLES LIKE 'product_categories'");
if ($category_table_check && mysqli_num_rows($category_table_check) > 0) {
    $category_table_ready = true;
}

$category_column_check = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'category_id'");
if ($category_column_check && mysqli_num_rows($category_column_check) > 0) {
    $category_column_ready = true;
}

// Filter Configuration
$filterConfig = [
    'search' => ['col' => 'p_name', 'type' => 'like'],
    'category' => ['col' => 'category_id', 'type' => 'equals']
];
$whereClause = buildSimpleWhere($con, $filterConfig);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_action'])) {
    // ... rest of action handling ...
    $open_categories_modal = true;
    $redirect_page = isset($_POST['redirect_page']) ? (int)$_POST['redirect_page'] : $requested_page;
    if ($redirect_page < 1) $redirect_page = 1;

    if (!$category_table_ready || !$category_column_ready) {
        $category_messages[] = 'Category setup is not complete. Run setup_product_categories_table.php once, then retry.';
    } else {
        $category_action = strtolower(trim((string)$_POST['category_action']));

        if ($category_action === 'add') {
            $category_name = trim((string)($_POST['category_name'] ?? ''));
            if ($category_name === '') {
                $category_messages[] = 'Category name is required.';
            } elseif (strlen($category_name) > 120) {
                $category_messages[] = 'Category name must be 120 characters or less.';
            } else {
                $stmt = mysqli_prepare($con, 'INSERT INTO product_categories (category_name) VALUES (?)');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 's', $category_name);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location:' . buildProductsRedirectUrl('success', 'Category added successfully!', true, $redirect_page));
                        exit();
                    }
                    if (mysqli_errno($con) === 1062) {
                        $category_messages[] = 'This category already exists.';
                    } else {
                        $category_messages[] = 'Could not add category. Please try again.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $category_messages[] = 'Could not prepare category insert query.';
                }
            }
        } elseif ($category_action === 'update') {
            $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
            $category_name = trim((string) ($_POST['category_name'] ?? ''));

            if ($category_id <= 0) {
                $category_messages[] = 'Invalid category selected for update.';
            } elseif ($category_name === '') {
                $category_messages[] = 'Category name is required.';
            } elseif (strlen($category_name) > 120) {
                $category_messages[] = 'Category name must be 120 characters or less.';
            } else {
                $stmt = mysqli_prepare($con, 'UPDATE product_categories SET category_name = ? WHERE category_id = ?');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'si', $category_name, $category_id);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location:' . buildProductsRedirectUrl('success', 'Category updated successfully!', true, $redirect_page));
                        exit();
                    }
                    if (mysqli_errno($con) === 1062) {
                        $category_messages[] = 'This category name is already in use.';
                    } else {
                        $category_messages[] = 'Could not update category. Please try again.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $category_messages[] = 'Could not prepare category update query.';
                }
            }
        } elseif ($category_action === 'delete') {
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
            if ($category_id <= 0) {
                $category_messages[] = 'Invalid category selected for delete.';
            } else {
                $stmt = mysqli_prepare($con, 'DELETE FROM product_categories WHERE category_id = ?');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'i', $category_id);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location:' . buildProductsRedirectUrl('success', 'Category deleted successfully!', true, $redirect_page));
                        exit();
                    }
                    $category_messages[] = 'Could not delete category. Please try again.';
                    mysqli_stmt_close($stmt);
                } else {
                    $category_messages[] = 'Could not prepare category delete query.';
                }
            }
        }
    }
}

$categories = [];
$category_lookup = [];
$category_options = ['' => 'All Categories'];
if ($category_table_ready) {
    $category_result = mysqli_query($con, 'SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC');
    if ($category_result) {
        while ($category_row = mysqli_fetch_assoc($category_result)) {
            $categories[] = $category_row;
            $category_lookup[(int) $category_row['category_id']] = $category_row['category_name'];
            $category_options[$category_row['category_id']] = $category_row['category_name'];
        }
    }
}

// Pagination logic
$records_per_page = 10;
$current_page = $requested_page;

$count_query = "SELECT COUNT(*) AS total FROM products $whereClause";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = (int) $count_row['total'];

$total_pages = max(1, (int) ceil($total_records / $records_per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $records_per_page;

$query = "SELECT * FROM products $whereClause ORDER BY p_id DESC LIMIT $offset, $records_per_page";
$all_product = $con->query($query);
$has_products = $all_product && mysqli_num_rows($all_product) > 0;
?>

<?php
renderAdminPageIntro(
    'Products',
    'Product Management',
    'Manage catalog items, pricing, stock levels, product details, and categories for the salon store.'
);
?>

<div class="main-content">
    <div class="content" style="background: transparent; box-shadow: none; padding: 0;">
        <?php
        $filters = [
            [
                'type' => 'text',
                'name' => 'search',
                'placeholder' => 'Search products...',
                'value' => $_GET['search'] ?? '',
                'label' => 'Product Name'
            ],
            [
                'type' => 'select',
                'name' => 'category',
                'label' => 'Category',
                'options' => $category_options,
                'value' => $_GET['category'] ?? ''
            ]
        ];
        renderFilters($filters);
        ?>
    </div>

    <div class="content">
        <?php foreach ($category_messages as $msg): ?>
            <div class="message"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>

        <div class="page-section-toolbar">
            <div style="display: flex; flex-direction: column;">
                <h2 style="margin: 0;">Product Catalog</h2>
                <?php if (!empty($whereClause)): ?>
                    <span class="filter-indicator" style="margin-top: 5px;">
                        <i class="fas fa-filter"></i> <strong><?php echo $total_records; ?></strong> products match filters
                    </span>
                <?php endif; ?>
            </div>
            <div class="product-toolbar-actions">
                <a href="combos.php" class="add-service-btn">
                    <i class="fas fa-layer-group"></i> Manage Combos
                </a>
                <button type="button" class="add-service-btn" onclick="openCategoryManagerModal()">
                    <i class="fas fa-tags"></i> Manage Categories
                </button>
                <a href="add_product.php" class="add-service-btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>

        <!-- Product List -->
        <div class="product-list">
            <?php if ($has_products): ?>
                <?php while ($row = mysqli_fetch_assoc($all_product)) { ?>
                    <?php
                    $original_price = (float) $row['p_price'];
                    $discount = isset($row['p_discount']) ? (float) $row['p_discount'] : 0;
                    $discounted_price = $original_price - (($original_price * $discount) / 100);

                    $product_category_name = 'Uncategorized';
                    if (
                        $category_column_ready &&
                        isset($row['category_id']) &&
                        $row['category_id'] !== null &&
                        $row['category_id'] !== ''
                    ) {
                        $category_id = (int) $row['category_id'];
                        $product_category_name = isset($category_lookup[$category_id]) ? $category_lookup[$category_id] : 'Unknown Category';
                    }
                    ?>
                    <div class="product-item">
                        <img src="../upload_product_photos/<?php echo htmlspecialchars($row['p_img']); ?>" alt="Product Image">
                        <div class="product-info">
                            <h2><?php echo htmlspecialchars($row['p_name']); ?></h2>
                            <p><?php echo htmlspecialchars($row['p_desc']); ?></p>
                            <p>
                                <?php if ($discount > 0): ?>
                                    <span style="text-decoration: line-through; color: #777;">₹ <?php echo number_format($original_price, 2); ?></span>
                                    <span style="color: var(--brand); font-weight: 700;">₹ <?php echo number_format($discounted_price, 2); ?></span>
                                    <small style="margin-left: 6px; color: #1e8e3e;"><?php echo number_format($discount, 0); ?>% OFF</small>
                                <?php else: ?>
                                    ₹ <?php echo number_format($original_price, 2); ?>
                                <?php endif; ?>
                                <i> ( <?php echo htmlspecialchars($row['p_size']); ?> )</i>
                            </p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($product_category_name); ?></p>
                            <p>Quantity: <?php echo (int) $row['p_quantity']; ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="update_product.php?id=<?php echo (int) $row['p_id']; ?>">
                                <button class="update">Edit</button>
                            </a>
                            <a href="delete_product.php?id=<?php echo (int) $row['p_id']; ?>">
                                <button class="delete">Delete</button>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            <?php else: ?>
                <p>No products found matching your filters.</p>
            <?php endif; ?>

            <?php 
            $params = $_GET;
            unset($params['page']);
            echo renderPagination($total_records, $current_page, $records_per_page, 'products.php', $params); 
            ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="category_manager_modal">
    <div class="modal-box category-manager-modal">
        <div class="modal-header">
            <h3>Manage Product Categories</h3>
            <button type="button" class="close-modal" onclick="closeCategoryManagerModal()">&times;</button>
        </div>

        <div class="category-modal-toolbar">
            <p>Keep category names clean and consistent for better product management.</p>
            <button type="button" class="add-service-btn" onclick="openCategoryFormModal('add')">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>

        <?php if (!$category_table_ready || !$category_column_ready): ?>
            <div class="category-setup-warning">
                <p>Category schema is not ready yet.</p>
                <p>
                    Run
                    <code>admin/setup_product_categories_table.php</code>
                    once, then reload this page.
                </p>
            </div>
        <?php elseif (!empty($categories)): ?>
            <div class="service-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th>Category Name</th>
                            <th style="width: 150px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo (int) $category['category_id']; ?></td>
                                <td class="category-name-cell"><?php echo htmlspecialchars($category['category_name']); ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button
                                            type="button"
                                            class="service-update"
                                            title="Edit category"
                                            aria-label="Edit category"
                                            onclick="openCategoryFormModal('edit', <?php echo (int) $category['category_id']; ?>, <?php echo htmlspecialchars(json_encode($category['category_name']), ENT_QUOTES, 'UTF-8'); ?>)"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="post" class="inline-action-form">
                                            <input type="hidden" name="category_action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo (int) $category['category_id']; ?>">
                                            <input type="hidden" name="redirect_page" value="<?php echo (int) $current_page; ?>">
                                            <button
                                                type="submit"
                                                class="service-delete"
                                                title="Delete category"
                                                aria-label="Delete category"
                                                onclick="return confirm('Delete this category? Linked products will become uncategorized.');"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="category-empty-state">No categories found. Click <strong>Add Category</strong> to create your first one.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="category_form_modal">
    <div class="modal-box category-form-modal">
        <div class="modal-header">
            <h3 id="category_form_title">Add Category</h3>
            <button type="button" class="close-modal" onclick="closeCategoryFormModal()">&times;</button>
        </div>

        <form class="modal-form" method="post" id="category_form">
            <input type="hidden" name="category_action" id="category_action" value="add">
            <input type="hidden" name="category_id" id="category_id" value="0">
            <input type="hidden" name="redirect_page" value="<?php echo (int) $current_page; ?>">

            <label for="category_name">Category Name</label>
            <input
                type="text"
                id="category_name"
                name="category_name"
                maxlength="120"
                placeholder="e.g. Hair Care"
                required
            >

            <button type="submit" class="modal-submit-btn" id="category_submit_btn">Save Category</button>
        </form>
    </div>
</div>

<script>
function openCategoryManagerModal() {
    const modal = document.getElementById('category_manager_modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeCategoryManagerModal() {
    const modal = document.getElementById('category_manager_modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function openCategoryFormModal(mode, categoryId = 0, categoryName = '') {
    const formModal = document.getElementById('category_form_modal');
    const actionInput = document.getElementById('category_action');
    const idInput = document.getElementById('category_id');
    const nameInput = document.getElementById('category_name');
    const title = document.getElementById('category_form_title');
    const submitBtn = document.getElementById('category_submit_btn');

    if (!formModal || !actionInput || !idInput || !nameInput || !title || !submitBtn) {
        return;
    }

    if (mode === 'edit') {
        actionInput.value = 'update';
        idInput.value = categoryId;
        nameInput.value = categoryName || '';
        title.textContent = 'Edit Category';
        submitBtn.textContent = 'Update Category';
    } else {
        actionInput.value = 'add';
        idInput.value = '0';
        nameInput.value = '';
        title.textContent = 'Add Category';
        submitBtn.textContent = 'Save Category';
    }

    formModal.classList.add('active');
    setTimeout(() => nameInput.focus(), 50);
}

function closeCategoryFormModal() {
    const modal = document.getElementById('category_form_modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const shouldOpenCategoryManager = <?php echo $open_categories_modal ? 'true' : 'false'; ?>;
    if (shouldOpenCategoryManager) {
        openCategoryManagerModal();
    }
});
</script>

