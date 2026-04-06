<?php
include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

function comboTableExists(mysqli $con, string $table): bool
{
    $table_safe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function getComboDiscountedPrice(float $price, float $discount): float
{
    $discount = max(0, min(100, $discount));
    return round($price - (($price * $discount) / 100), 2);
}

function buildComboRedirectUrl(string $type, string $message, int $page = 1): string
{
    $params = [
        'toast' => $type,
        'msg' => $message
    ];
    if ($page > 1) {
        $params['page'] = $page;
    }
    return 'combos.php?' . http_build_query($params);
}

function normalizeComboProducts($decoded): array
{
    $normalized = [];
    if (!is_array($decoded)) {
        return $normalized;
    }

    $is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
    if ($is_assoc) {
        foreach ($decoded as $key => $qty) {
            $product_id = (int) $key;
            $quantity = (int) $qty;
            if ($product_id > 0 && $quantity > 0) {
                $normalized[$product_id] = $quantity;
            }
        }
    } else {
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $product_id = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            if ($product_id > 0 && $quantity > 0) {
                $normalized[$product_id] = $quantity;
            }
        }
    }

    return $normalized;
}

$requested_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($requested_page < 1) {
    $requested_page = 1;
}

$combo_messages = [];
$combos_table_ready = comboTableExists($con, 'combos');
$combo_products_table_ready = comboTableExists($con, 'combo_products');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['combo_action'])) {
    $redirect_page = isset($_POST['redirect_page']) ? (int) $_POST['redirect_page'] : $requested_page;
    if ($redirect_page < 1) {
        $redirect_page = 1;
    }

    if (!$combos_table_ready || !$combo_products_table_ready) {
        $combo_messages[] = 'Combo setup is not complete. Run setup_combo_management.php once and retry.';
    } else {
        $action = strtolower(trim((string) $_POST['combo_action']));

        if ($action === 'delete') {
            $combo_id = isset($_POST['combo_id']) ? (int) $_POST['combo_id'] : 0;
            if ($combo_id <= 0) {
                $combo_messages[] = 'Invalid combo selected for deletion.';
            } else {
                $delete_stmt = mysqli_prepare($con, 'DELETE FROM combos WHERE id = ?');
                if ($delete_stmt) {
                    mysqli_stmt_bind_param($delete_stmt, 'i', $combo_id);
                    if (mysqli_stmt_execute($delete_stmt)) {
                        mysqli_stmt_close($delete_stmt);
                        header('Location:' . buildComboRedirectUrl('success', 'Combo deleted successfully!', $redirect_page));
                        exit();
                    }
                    $combo_messages[] = 'Could not delete combo. Please try again.';
                    mysqli_stmt_close($delete_stmt);
                } else {
                    $combo_messages[] = 'Could not prepare delete query.';
                }
            }
        } elseif ($action === 'add' || $action === 'update') {
            $combo_id = isset($_POST['combo_id']) ? (int) $_POST['combo_id'] : 0;
            $name = trim((string) ($_POST['combo_name'] ?? ''));
            $description = trim((string) ($_POST['combo_description'] ?? ''));
            $price = isset($_POST['combo_price']) ? (float) $_POST['combo_price'] : 0;
            $status = isset($_POST['combo_status']) ? (int) $_POST['combo_status'] : 1;
            $status = $status === 0 ? 0 : 1;
            $existing_image = trim((string) ($_POST['existing_image'] ?? ''));

            $products_json = (string) ($_POST['combo_products_json'] ?? '[]');
            $decoded_products = json_decode($products_json, true);
            $selected_products = normalizeComboProducts($decoded_products);

            if ($name === '') {
                $combo_messages[] = 'Combo name is required.';
            } elseif (strlen($name) > 255) {
                $combo_messages[] = 'Combo name must be 255 characters or less.';
            } elseif ($price <= 0) {
                $combo_messages[] = 'Combo price must be greater than zero.';
            } elseif (empty($selected_products)) {
                $combo_messages[] = 'Select at least one product for the combo.';
            }

            $image_to_store = $existing_image;
            $uploaded_image_tmp = '';
            $uploaded_target_path = '';
            $has_new_image = isset($_FILES['combo_image']) && isset($_FILES['combo_image']['error']) && (int) $_FILES['combo_image']['error'] === 0;

            if ($has_new_image) {
                if (!empty($_FILES['combo_image']['size']) && (int) $_FILES['combo_image']['size'] > 5000000) {
                    $combo_messages[] = 'Combo image is too large. Max size is 5MB.';
                } else {
                    $original_name = (string) $_FILES['combo_image']['name'];
                    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($extension, $allowed, true)) {
                        $combo_messages[] = 'Only JPG, JPEG, PNG, or WEBP images are allowed.';
                    } else {
                        $safe_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($original_name));
                        $image_to_store = time() . '_combo_' . $safe_name;
                        $uploaded_image_tmp = (string) $_FILES['combo_image']['tmp_name'];
                        $uploaded_target_path = '../upload_product_photos/' . $image_to_store;
                    }
                }
            } elseif ($action === 'add' && $image_to_store === '') {
                $combo_messages[] = 'Combo image is required.';
            }

            $valid_product_ids = [];
            if (empty($combo_messages) && !empty($selected_products)) {
                $id_list_sql = implode(',', array_map('intval', array_keys($selected_products)));
                $valid_query = mysqli_query($con, "SELECT p_id FROM products WHERE p_id IN ({$id_list_sql})");
                if ($valid_query) {
                    while ($valid_row = mysqli_fetch_assoc($valid_query)) {
                        $valid_product_ids[(int) $valid_row['p_id']] = true;
                    }
                }

                foreach ($selected_products as $product_id => $quantity) {
                    if (!isset($valid_product_ids[$product_id])) {
                        $combo_messages[] = 'One or more selected products are invalid.';
                        break;
                    }
                    if ($quantity <= 0) {
                        $combo_messages[] = 'Product quantity must be at least 1.';
                        break;
                    }
                }
            }

            if (empty($combo_messages)) {
                mysqli_begin_transaction($con);
                try {
                    $name_db = mysqli_real_escape_string($con, $name);
                    $description_db = mysqli_real_escape_string($con, $description);
                    $image_db = mysqli_real_escape_string($con, $image_to_store);

                    if ($action === 'add') {
                        $insert_combo_sql = "INSERT INTO combos (name, description, price, image, status) VALUES ('{$name_db}', '{$description_db}', {$price}, '{$image_db}', {$status})";
                        if (!mysqli_query($con, $insert_combo_sql)) {
                            throw new RuntimeException('Failed to create combo: ' . mysqli_error($con));
                        }
                        $combo_id = (int) mysqli_insert_id($con);
                    } else {
                        if ($combo_id <= 0) {
                            throw new RuntimeException('Invalid combo selected for update.');
                        }
                        $update_combo_sql = "UPDATE combos SET name = '{$name_db}', description = '{$description_db}', price = {$price}, image = '{$image_db}', status = {$status} WHERE id = {$combo_id}";
                        if (!mysqli_query($con, $update_combo_sql)) {
                            throw new RuntimeException('Failed to update combo: ' . mysqli_error($con));
                        }
                        if (!mysqli_query($con, "DELETE FROM combo_products WHERE combo_id = {$combo_id}")) {
                            throw new RuntimeException('Failed to reset combo products: ' . mysqli_error($con));
                        }
                    }

                    $insert_combo_product_stmt = mysqli_prepare(
                        $con,
                        'INSERT INTO combo_products (combo_id, product_id, quantity) VALUES (?, ?, ?)'
                    );
                    if (!$insert_combo_product_stmt) {
                        throw new RuntimeException('Failed to prepare combo product insert: ' . mysqli_error($con));
                    }

                    foreach ($selected_products as $product_id => $quantity) {
                        mysqli_stmt_bind_param($insert_combo_product_stmt, 'iii', $combo_id, $product_id, $quantity);
                        if (!mysqli_stmt_execute($insert_combo_product_stmt)) {
                            $error = mysqli_error($con);
                            mysqli_stmt_close($insert_combo_product_stmt);
                            throw new RuntimeException('Failed to save combo products: ' . $error);
                        }
                    }
                    mysqli_stmt_close($insert_combo_product_stmt);

                    if ($has_new_image && $uploaded_image_tmp !== '' && $uploaded_target_path !== '') {
                        if (!move_uploaded_file($uploaded_image_tmp, $uploaded_target_path)) {
                            throw new RuntimeException('Failed to upload combo image.');
                        }
                    }

                    mysqli_commit($con);
                    $success_message = $action === 'add' ? 'Combo created successfully!' : 'Combo updated successfully!';
                    header('Location:' . buildComboRedirectUrl('success', $success_message, $redirect_page));
                    exit();
                } catch (Throwable $e) {
                    mysqli_rollback($con);
                    $combo_messages[] = $e->getMessage();
                }
            }
        } else {
            $combo_messages[] = 'Invalid combo action.';
        }
    }
}

$product_catalog = [];
if ($combos_table_ready && $combo_products_table_ready) {
    $product_query = mysqli_query(
        $con,
        'SELECT p_id, p_name, p_price, p_discount, p_quantity, p_img, p_size FROM products ORDER BY p_name ASC'
    );
    if ($product_query) {
        while ($product_row = mysqli_fetch_assoc($product_query)) {
            $base_price = (float) $product_row['p_price'];
            $discount = isset($product_row['p_discount']) ? (float) $product_row['p_discount'] : 0;
            $final_price = getComboDiscountedPrice($base_price, $discount);
            $product_catalog[] = [
                'id' => (int) $product_row['p_id'],
                'name' => $product_row['p_name'],
                'size' => $product_row['p_size'],
                'price' => $base_price,
                'discount' => $discount,
                'final_price' => $final_price,
                'stock' => (int) $product_row['p_quantity'],
                'image' => $product_row['p_img']
            ];
        }
    }
}

$records_per_page = 10;
$current_page = $requested_page;
$total_records = 0;
$combo_rows = [];
$combo_products_map = [];
$combo_original_totals = [];

if ($combos_table_ready) {
    $count_result = mysqli_query($con, 'SELECT COUNT(*) AS total FROM combos');
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_records = (int) ($count_row['total'] ?? 0);
    }

    $total_pages = max(1, (int) ceil($total_records / $records_per_page));
    if ($current_page > $total_pages) {
        $current_page = $total_pages;
    }
    $offset = ($current_page - 1) * $records_per_page;

    $list_query = mysqli_query(
        $con,
        "SELECT * FROM combos ORDER BY id DESC LIMIT {$offset}, {$records_per_page}"
    );
    if ($list_query) {
        while ($combo = mysqli_fetch_assoc($list_query)) {
            $combo_rows[] = $combo;
        }
    }

    $combo_ids = [];
    foreach ($combo_rows as $combo) {
        $combo_ids[] = (int) $combo['id'];
    }

    if (!empty($combo_ids)) {
        $combo_id_sql = implode(',', $combo_ids);
        $combo_products_query = mysqli_query(
            $con,
            "SELECT cp.combo_id, cp.product_id, cp.quantity, p.p_name, p.p_price, p.p_discount, p.p_img, p.p_size, p.p_quantity
             FROM combo_products cp
             INNER JOIN products p ON cp.product_id = p.p_id
             WHERE cp.combo_id IN ({$combo_id_sql})
             ORDER BY cp.combo_id ASC, p.p_name ASC"
        );

        if ($combo_products_query) {
            while ($cp_row = mysqli_fetch_assoc($combo_products_query)) {
                $combo_id = (int) $cp_row['combo_id'];
                $quantity = (int) $cp_row['quantity'];
                $base_price = (float) $cp_row['p_price'];
                $discount = isset($cp_row['p_discount']) ? (float) $cp_row['p_discount'] : 0;
                $final_price = getComboDiscountedPrice($base_price, $discount);

                if (!isset($combo_products_map[$combo_id])) {
                    $combo_products_map[$combo_id] = [];
                }
                $combo_products_map[$combo_id][] = [
                    'product_id' => (int) $cp_row['product_id'],
                    'name' => $cp_row['p_name'],
                    'size' => $cp_row['p_size'],
                    'image' => $cp_row['p_img'],
                    'unit_price' => $base_price,
                    'final_price' => $final_price,
                    'quantity' => $quantity,
                    'stock' => (int) $cp_row['p_quantity']
                ];

                if (!isset($combo_original_totals[$combo_id])) {
                    $combo_original_totals[$combo_id] = 0;
                }
                $combo_original_totals[$combo_id] += ($final_price * $quantity);
            }
        }
    }
}

renderAdminPageIntro(
    'Products / Manage Combos',
    'Premium Combo Management',
    'Create curated product bundles, control pricing, and manage combo availability with product-level quantities.'
);
?>
<div class="main-content">
    <div class="content combo-management-content">
        <?php foreach ($combo_messages as $msg): ?>
            <div class="message"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>

        <div class="page-section-toolbar">
            <h2>Combo Listings</h2>
            <button type="button" class="add-service-btn" onclick="openAddComboModal()">
                <i class="fas fa-plus"></i> Add Premium Combo
            </button>
        </div>

        <?php if (!$combos_table_ready || !$combo_products_table_ready): ?>
            <div class="category-setup-warning">
                <p>Combo schema is not ready yet.</p>
                <p>Run <code>admin/setup_combo_management.php</code> once, then reload this page.</p>
            </div>
        <?php elseif (empty($combo_rows)): ?>
            <p class="category-empty-state">No combos found. Click <strong>Add Premium Combo</strong> to create your first combo bundle.</p>
        <?php else: ?>
            <div class="service-table-wrapper combo-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Combo</th>
                            <th>Included Products</th>
                            <th style="width: 120px;">Price</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 150px;">Updated</th>
                            <th style="width: 150px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($combo_rows as $combo): ?>
                            <?php
                                $combo_id = (int) $combo['id'];
                                $combo_products = isset($combo_products_map[$combo_id]) ? $combo_products_map[$combo_id] : [];
                                $original_total = isset($combo_original_totals[$combo_id]) ? (float) $combo_original_totals[$combo_id] : 0;
                                $combo_price = (float) $combo['price'];
                                $savings = max(0, $original_total - $combo_price);
                                $combo_payload = [
                                    'id' => $combo_id,
                                    'name' => $combo['name'],
                                    'description' => $combo['description'],
                                    'price' => $combo_price,
                                    'image' => $combo['image'],
                                    'status' => (int) $combo['status'],
                                    'original_total' => $original_total,
                                    'savings' => $savings,
                                    'products' => $combo_products
                                ];
                            ?>
                            <tr>
                                <td><?php echo $combo_id; ?></td>
                                <td>
                                    <div class="combo-title-cell">
                                        <div class="combo-thumb">
                                            <?php if (!empty($combo['image'])): ?>
                                                <img src="../upload_product_photos/<?php echo htmlspecialchars($combo['image']); ?>" alt="Combo Image">
                                            <?php else: ?>
                                                <i class="fas fa-box-open"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="combo-title-copy">
                                            <div class="combo-name"><?php echo htmlspecialchars($combo['name']); ?></div>
                                            <div class="combo-desc"><?php echo htmlspecialchars($combo['description'] ?: 'No description added yet.'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($combo_products)): ?>
                                        <div class="combo-product-chips">
                                            <?php foreach ($combo_products as $item): ?>
                                                <span class="combo-product-chip">
                                                    <?php echo htmlspecialchars($item['name']); ?> × <?php echo (int) $item['quantity']; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="combo-empty-products">No products linked</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="combo-price-cell">
                                        <strong>₹ <?php echo number_format($combo_price, 2); ?></strong>
                                        <?php if ($savings > 0): ?>
                                            <small>Save ₹ <?php echo number_format($savings, 2); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ((int) $combo['status'] === 1): ?>
                                        <span class="combo-status-badge active">Active</span>
                                    <?php else: ?>
                                        <span class="combo-status-badge inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($combo['updated_at'] ?? $combo['created_at']))); ?></td>
                                <td>
                                    <div class="services-buttons">
                                        <button
                                            type="button"
                                            class="service-update"
                                            title="View combo"
                                            aria-label="View combo"
                                            data-combo='<?php echo htmlspecialchars(json_encode($combo_payload), ENT_QUOTES, 'UTF-8'); ?>'
                                            onclick="openViewComboModal(this)"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="service-update"
                                            title="Edit combo"
                                            aria-label="Edit combo"
                                            data-combo='<?php echo htmlspecialchars(json_encode($combo_payload), ENT_QUOTES, 'UTF-8'); ?>'
                                            onclick="openEditComboModal(this)"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="inline-action-form">
                                            <input type="hidden" name="combo_action" value="delete">
                                            <input type="hidden" name="combo_id" value="<?php echo $combo_id; ?>">
                                            <input type="hidden" name="redirect_page" value="<?php echo (int) $current_page; ?>">
                                            <button
                                                type="submit"
                                                class="service-delete"
                                                title="Delete combo"
                                                aria-label="Delete combo"
                                                onclick="return confirm('Delete this combo? This will also remove linked combo products and combo cart items.');"
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
            <?php echo renderPagination($total_records, $current_page, $records_per_page, 'combos.php'); ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="combo_form_modal">
    <div class="modal-box combo-form-modal">
        <div class="modal-header">
            <h3 id="combo_form_title">Add Premium Combo</h3>
            <button type="button" class="close-modal" onclick="closeComboFormModal()">&times;</button>
        </div>

        <form class="modal-form combo-modal-form" method="post" enctype="multipart/form-data" id="combo_form">
            <input type="hidden" name="combo_action" id="combo_action" value="add">
            <input type="hidden" name="combo_id" id="combo_id" value="0">
            <input type="hidden" name="redirect_page" value="<?php echo (int) $current_page; ?>">
            <input type="hidden" name="existing_image" id="existing_image" value="">
            <input type="hidden" name="combo_products_json" id="combo_products_json" value="{}">

            <label for="combo_name">Combo Name</label>
            <input type="text" id="combo_name" name="combo_name" maxlength="255" placeholder="e.g. Royal Grooming Starter Combo" required>

            <label for="combo_description">Description</label>
            <textarea id="combo_description" name="combo_description" rows="3" placeholder="Describe what this premium combo includes and why it is special."></textarea>

            <div class="combo-form-grid">
                <div>
                    <label for="combo_price">Combo Price</label>
                    <input type="number" id="combo_price" name="combo_price" min="0.01" step="0.01" placeholder="0.00" required>
                </div>
                <div>
                    <label for="combo_status">Status</label>
                    <select id="combo_status" name="combo_status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <label for="combo_image">Combo Image</label>
            <input type="file" id="combo_image" name="combo_image" accept=".jpg,.jpeg,.png,.webp">
            <small class="combo-field-hint">Recommended size: 1000x1000px. Max: 5MB.</small>

            <div id="combo_image_preview_wrap" class="combo-image-preview-wrap">
                <img id="combo_image_preview" src="" alt="Combo Preview">
            </div>

            <div class="combo-products-select-box">
                <div class="combo-products-select-head">
                    <h4>Selected Products</h4>
                    <button type="button" class="add-service-btn" onclick="openProductSelectorModal()">
                        <i class="fas fa-box-open"></i> Select Products
                    </button>
                </div>
                <div class="combo-products-selected-list" id="selected_products_summary">
                    <p class="combo-selection-empty">No products selected yet.</p>
                </div>
            </div>

            <button type="submit" class="modal-submit-btn" id="combo_submit_btn">Create Combo</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="product_selector_modal">
    <div class="modal-box combo-products-modal">
        <div class="modal-header">
            <h3>Select Products For Combo</h3>
            <button type="button" class="close-modal" onclick="closeProductSelectorModal()">&times;</button>
        </div>

        <p class="combo-selector-note">Choose products and set quantity per product for this combo bundle.</p>

        <div class="combo-product-picker-grid" id="combo_product_picker_grid">
            <?php if (empty($product_catalog)): ?>
                <p class="combo-selection-empty">No products available. Add products first, then create combos.</p>
            <?php else: ?>
                <?php foreach ($product_catalog as $product): ?>
                    <div
                        class="combo-product-picker-card"
                        data-product-id="<?php echo (int) $product['id']; ?>"
                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                        data-product-size="<?php echo htmlspecialchars($product['size']); ?>"
                        data-product-price="<?php echo number_format((float) $product['price'], 2, '.', ''); ?>"
                        data-product-final-price="<?php echo number_format((float) $product['final_price'], 2, '.', ''); ?>"
                        data-product-stock="<?php echo (int) $product['stock']; ?>"
                    >
                        <label class="combo-product-picker-check">
                            <input type="checkbox" class="combo-product-checkbox">
                            <span>Select</span>
                        </label>
                        <div class="combo-product-picker-media">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../upload_product_photos/<?php echo htmlspecialchars($product['image']); ?>" alt="Product">
                            <?php else: ?>
                                <i class="fas fa-box"></i>
                            <?php endif; ?>
                        </div>
                        <div class="combo-product-picker-info">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p>Size: <?php echo htmlspecialchars($product['size']); ?></p>
                            <p>Price: ₹ <?php echo number_format((float) $product['final_price'], 2); ?></p>
                            <p>Stock: <?php echo (int) $product['stock']; ?></p>
                        </div>
                        <div class="combo-product-picker-qty">
                            <label>Qty</label>
                            <input type="number" class="combo-product-qty-input" min="1" value="1">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="combo-selector-actions">
            <button type="button" class="modal-submit-btn" onclick="applyProductSelection()">Apply Selection</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="combo_view_modal">
    <div class="modal-box combo-view-modal">
        <div class="modal-header">
            <h3>Combo Details</h3>
            <button type="button" class="close-modal" onclick="closeViewComboModal()">&times;</button>
        </div>

        <div class="combo-view-body">
            <div class="combo-view-top">
                <div class="combo-view-image" id="view_combo_image_wrap">
                    <img id="view_combo_image" src="" alt="Combo Image">
                </div>
                <div class="combo-view-content">
                    <h4 id="view_combo_name"></h4>
                    <p id="view_combo_desc"></p>
                    <div class="combo-view-price-row">
                        <strong id="view_combo_price"></strong>
                        <small id="view_combo_savings"></small>
                    </div>
                    <span id="view_combo_status" class="combo-status-badge active">Active</span>
                </div>
            </div>
            <div class="combo-view-products">
                <h5>Included Products</h5>
                <div id="view_combo_products_list"></div>
            </div>
        </div>
    </div>
</div>

<script>
const selectedProducts = {};

function parseComboPayload(button) {
    if (!button || !button.dataset || !button.dataset.combo) {
        return null;
    }
    try {
        return JSON.parse(button.dataset.combo);
    } catch (err) {
        return null;
    }
}

function openAddComboModal() {
    document.getElementById('combo_form_title').textContent = 'Add Premium Combo';
    document.getElementById('combo_submit_btn').textContent = 'Create Combo';
    document.getElementById('combo_action').value = 'add';
    document.getElementById('combo_id').value = '0';
    document.getElementById('combo_name').value = '';
    document.getElementById('combo_description').value = '';
    document.getElementById('combo_price').value = '';
    document.getElementById('combo_status').value = '1';
    document.getElementById('existing_image').value = '';
    document.getElementById('combo_image').value = '';
    clearSelectedProducts();
    setComboImagePreview('');
    document.getElementById('combo_form_modal').classList.add('active');
    setTimeout(() => {
        document.getElementById('combo_name').focus();
    }, 60);
}

function openEditComboModal(button) {
    const combo = parseComboPayload(button);
    if (!combo) {
        return;
    }

    document.getElementById('combo_form_title').textContent = 'Edit Premium Combo';
    document.getElementById('combo_submit_btn').textContent = 'Update Combo';
    document.getElementById('combo_action').value = 'update';
    document.getElementById('combo_id').value = String(combo.id || 0);
    document.getElementById('combo_name').value = combo.name || '';
    document.getElementById('combo_description').value = combo.description || '';
    document.getElementById('combo_price').value = combo.price ? Number(combo.price).toFixed(2) : '';
    document.getElementById('combo_status').value = String(combo.status === 0 ? 0 : 1);
    document.getElementById('existing_image').value = combo.image || '';
    document.getElementById('combo_image').value = '';
    setComboImagePreview(combo.image ? '../upload_product_photos/' + combo.image : '');

    clearSelectedProducts();
    if (Array.isArray(combo.products)) {
        combo.products.forEach((item) => {
            const pid = Number(item.product_id || 0);
            const qty = Number(item.quantity || 0);
            if (pid > 0 && qty > 0) {
                selectedProducts[pid] = qty;
            }
        });
    }
    syncComboProductsInput();
    renderSelectedProductsSummary();
    syncProductPickerSelection();

    document.getElementById('combo_form_modal').classList.add('active');
}

function closeComboFormModal() {
    const modal = document.getElementById('combo_form_modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function openProductSelectorModal() {
    syncProductPickerSelection();
    const modal = document.getElementById('product_selector_modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeProductSelectorModal() {
    const modal = document.getElementById('product_selector_modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function openViewComboModal(button) {
    const combo = parseComboPayload(button);
    if (!combo) {
        return;
    }

    const nameEl = document.getElementById('view_combo_name');
    const descEl = document.getElementById('view_combo_desc');
    const priceEl = document.getElementById('view_combo_price');
    const savingsEl = document.getElementById('view_combo_savings');
    const statusEl = document.getElementById('view_combo_status');
    const imageWrapEl = document.getElementById('view_combo_image_wrap');
    const imageEl = document.getElementById('view_combo_image');
    const listEl = document.getElementById('view_combo_products_list');

    nameEl.textContent = combo.name || 'Premium Combo';
    descEl.textContent = combo.description || 'No description available.';
    priceEl.textContent = '₹ ' + Number(combo.price || 0).toFixed(2);
    const savings = Number(combo.savings || 0);
    savingsEl.textContent = savings > 0 ? ('Save ₹ ' + savings.toFixed(2)) : '';

    if (Number(combo.status) === 1) {
        statusEl.classList.remove('inactive');
        statusEl.classList.add('active');
        statusEl.textContent = 'Active';
    } else {
        statusEl.classList.remove('active');
        statusEl.classList.add('inactive');
        statusEl.textContent = 'Inactive';
    }

    if (combo.image) {
        imageWrapEl.classList.remove('no-image');
        imageEl.src = '../upload_product_photos/' + combo.image;
        imageEl.style.display = 'block';
    } else {
        imageWrapEl.classList.add('no-image');
        imageEl.style.display = 'none';
    }

    listEl.innerHTML = '';
    if (Array.isArray(combo.products) && combo.products.length > 0) {
        combo.products.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'combo-view-product-row';
            const itemName = item.name || 'Product';
            const itemSize = item.size || '';
            const itemQty = Number(item.quantity || 0);
            const itemPrice = Number(item.final_price || item.unit_price || 0);
            const itemImage = item.image ? ('../upload_product_photos/' + item.image) : '../upload_product_photos/default.jpeg';
            row.innerHTML = `
                <div class="combo-view-product-main">
                    <img src="${itemImage}" alt="${itemName}" class="combo-view-product-thumb">
                    <div>
                        <strong>${itemName}</strong>
                        <span>${itemSize ? 'Size: ' + itemSize + ' | ' : ''}Qty: ${itemQty}</span>
                    </div>
                </div>
                <div>₹ ${(itemPrice * itemQty).toFixed(2)}</div>
            `;
            listEl.appendChild(row);
        });
    } else {
        listEl.innerHTML = '<p class="combo-selection-empty">No products linked in this combo.</p>';
    }

    document.getElementById('combo_view_modal').classList.add('active');
}

function closeViewComboModal() {
    const modal = document.getElementById('combo_view_modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function clearSelectedProducts() {
    Object.keys(selectedProducts).forEach((key) => {
        delete selectedProducts[key];
    });
    syncComboProductsInput();
    renderSelectedProductsSummary();
    syncProductPickerSelection();
}

function syncComboProductsInput() {
    document.getElementById('combo_products_json').value = JSON.stringify(selectedProducts);
}

function renderSelectedProductsSummary() {
    const summary = document.getElementById('selected_products_summary');
    if (!summary) {
        return;
    }

    const cards = Array.from(document.querySelectorAll('.combo-product-picker-card'));
    const selectedEntries = Object.keys(selectedProducts);
    if (selectedEntries.length === 0) {
        summary.innerHTML = '<p class="combo-selection-empty">No products selected yet.</p>';
        syncComboProductsInput();
        return;
    }

    const chunks = [];
    selectedEntries.forEach((id) => {
        const card = cards.find((el) => Number(el.dataset.productId) === Number(id));
        if (!card) {
            return;
        }
        const name = card.dataset.productName || 'Product';
        const price = Number(card.dataset.productFinalPrice || 0);
        const qty = Number(selectedProducts[id] || 0);
        chunks.push(`
            <div class="combo-selected-chip">
                <span>${name}</span>
                <small>Qty: ${qty} | ₹ ${(price * qty).toFixed(2)}</small>
                <button type="button" onclick="removeSelectedProduct(${Number(id)})" aria-label="Remove selected product">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
    });

    if (chunks.length === 0) {
        summary.innerHTML = '<p class="combo-selection-empty">No products selected yet.</p>';
    } else {
        summary.innerHTML = chunks.join('');
    }
    syncComboProductsInput();
}

function removeSelectedProduct(productId) {
    delete selectedProducts[productId];
    syncProductPickerSelection();
    renderSelectedProductsSummary();
}

function syncProductPickerSelection() {
    const cards = document.querySelectorAll('.combo-product-picker-card');
    cards.forEach((card) => {
        const productId = Number(card.dataset.productId || 0);
        const checkbox = card.querySelector('.combo-product-checkbox');
        const qtyInput = card.querySelector('.combo-product-qty-input');
        if (!checkbox || !qtyInput || productId <= 0) {
            return;
        }

        if (Object.prototype.hasOwnProperty.call(selectedProducts, productId)) {
            checkbox.checked = true;
            qtyInput.value = String(selectedProducts[productId]);
            card.classList.add('selected');
        } else {
            checkbox.checked = false;
            qtyInput.value = '1';
            card.classList.remove('selected');
        }
    });
}

function applyProductSelection() {
    const cards = document.querySelectorAll('.combo-product-picker-card');
    Object.keys(selectedProducts).forEach((key) => delete selectedProducts[key]);

    cards.forEach((card) => {
        const checkbox = card.querySelector('.combo-product-checkbox');
        const qtyInput = card.querySelector('.combo-product-qty-input');
        const productId = Number(card.dataset.productId || 0);
        if (!checkbox || !qtyInput || productId <= 0) {
            return;
        }

        if (checkbox.checked) {
            const qty = Math.max(1, Number(qtyInput.value || 1));
            selectedProducts[productId] = qty;
        }
    });

    renderSelectedProductsSummary();
    syncProductPickerSelection();
    closeProductSelectorModal();
}

function setComboImagePreview(path) {
    const wrap = document.getElementById('combo_image_preview_wrap');
    const image = document.getElementById('combo_image_preview');
    if (!wrap || !image) {
        return;
    }

    if (path) {
        image.src = path;
        wrap.classList.add('visible');
    } else {
        image.src = '';
        wrap.classList.remove('visible');
    }
}

document.getElementById('combo_image').addEventListener('change', function(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            setComboImagePreview(e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
});

document.getElementById('combo_form').addEventListener('submit', function(event) {
    if (Object.keys(selectedProducts).length === 0) {
        event.preventDefault();
        if (typeof showToast === 'function') {
            showToast('Please select at least one product for the combo.', 'error');
        }
        openProductSelectorModal();
        return;
    }
    syncComboProductsInput();
});

document.addEventListener('change', function(event) {
    if (event.target.classList.contains('combo-product-checkbox')) {
        const card = event.target.closest('.combo-product-picker-card');
        if (!card) {
            return;
        }
        card.classList.toggle('selected', event.target.checked);
    }
});

window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
    }
});
</script>
