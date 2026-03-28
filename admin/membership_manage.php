<?php
include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');

$membership_config = [
    'royal' => [
        'title' => 'Royal Pass',
        'description' => 'Premium membership with exclusive offers and priority benefits.',
        'table' => 'royal_membership',
        'id_col' => 'royal_id',
        'plan_col' => 'royal_plan',
        'desc_col' => 'royal_desc',
        'price_col' => 'royal_price'
    ],
    'classic' => [
        'title' => 'Classic Pass',
        'description' => 'Balanced membership with solid discounts and recurring perks.',
        'table' => 'classic_membership',
        'id_col' => 'classic_id',
        'plan_col' => 'classic_plan',
        'desc_col' => 'classic_desc',
        'price_col' => 'classic_price'
    ],
    'standard' => [
        'title' => 'Standard Pass',
        'description' => 'Entry membership with essential savings and member-only access.',
        'table' => 'standard_membership',
        'id_col' => 'standard_id',
        'plan_col' => 'standard_plan',
        'desc_col' => 'standard_desc',
        'price_col' => 'standard_price'
    ]
];

$messages = [];
$records_per_page = 10;
$active_tab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'royal';
if (!isset($membership_config[$active_tab])) {
    $active_tab = 'royal';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_action'], $_POST['membership_type'])) {
    $action = strtolower(trim($_POST['membership_action']));
    $type = strtolower(trim($_POST['membership_type']));

    if (!isset($membership_config[$type])) {
        $messages[] = 'Invalid membership type selected.';
    } else {
        $active_tab = $type;
        $cfg = $membership_config[$type];

        $plan_raw = isset($_POST['membership_plan']) ? strtolower(trim($_POST['membership_plan'])) : '';
        $desc_raw = isset($_POST['membership_desc']) ? trim($_POST['membership_desc']) : '';
        $price_raw = isset($_POST['membership_price']) ? trim($_POST['membership_price']) : '';

        $allowed_plans = ['yearly', 'monthly'];
        $plan = in_array($plan_raw, $allowed_plans, true) ? $plan_raw : '';
        $desc = mysqli_real_escape_string($con, $desc_raw);
        $price = is_numeric($price_raw) ? (int) $price_raw : -1;

        if ($action === 'add') {
            if ($plan === '' || $desc === '' || $price < 0) {
                $messages[] = 'Please fill plan, description and price correctly.';
            } else {
                $insert_query = "INSERT INTO {$cfg['table']} ({$cfg['plan_col']}, {$cfg['desc_col']}, {$cfg['price_col']}) VALUES ('$plan', '$desc', $price)";
                if (mysqli_query($con, $insert_query)) {
                    header('Location: membership_manage.php?tab=' . $type . '&toast=success&msg=' . urlencode('Membership feature added successfully!'));
                    exit();
                }
                $messages[] = 'Could not add membership feature.';
            }
        } elseif ($action === 'update') {
            $id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
            if ($id <= 0 || $plan === '' || $desc === '' || $price < 0) {
                $messages[] = 'Please fill all update fields correctly.';
            } else {
                $update_query = "UPDATE {$cfg['table']} SET {$cfg['plan_col']}='$plan', {$cfg['desc_col']}='$desc', {$cfg['price_col']}=$price WHERE {$cfg['id_col']}=$id";
                if (mysqli_query($con, $update_query)) {
                    header('Location: membership_manage.php?tab=' . $type . '&toast=success&msg=' . urlencode('Membership feature updated successfully!'));
                    exit();
                }
                $messages[] = 'Could not update membership feature.';
            }
        } elseif ($action === 'delete') {
            $id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
            if ($id <= 0) {
                $messages[] = 'Invalid feature selected for deletion.';
            } else {
                $delete_query = "DELETE FROM {$cfg['table']} WHERE {$cfg['id_col']}=$id LIMIT 1";
                if (mysqli_query($con, $delete_query)) {
                    header('Location: membership_manage.php?tab=' . $type . '&toast=success&msg=' . urlencode('Membership feature deleted successfully!'));
                    exit();
                }
                $messages[] = 'Could not delete membership feature.';
            }
        } else {
            $messages[] = 'Invalid membership action.';
        }
    }
}

$membership_rows = [];
$membership_pagination = [];
foreach ($membership_config as $type => $cfg) {
    $page_param = $type . '_page';
    $current_tab_page = isset($_GET[$page_param]) ? (int) $_GET[$page_param] : 1;
    if ($current_tab_page < 1) {
        $current_tab_page = 1;
    }

    $count_query = "SELECT COUNT(*) AS total FROM {$cfg['table']}";
    $count_result = mysqli_query($con, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = (int) $count_row['total'];

    $total_pages = max(1, (int) ceil($total_records / $records_per_page));
    if ($current_tab_page > $total_pages) {
        $current_tab_page = $total_pages;
    }

    $offset = ($current_tab_page - 1) * $records_per_page;

    $membership_rows[$type] = [];
    $result = mysqli_query($con, "SELECT * FROM {$cfg['table']} ORDER BY {$cfg['id_col']} DESC LIMIT $offset, $records_per_page");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $membership_rows[$type][] = $row;
        }
    }

    $membership_pagination[$type] = [
        'total_records' => $total_records,
        'current_page' => $current_tab_page,
        'page_param' => $page_param
    ];
}
?>

<?php
renderAdminPageIntro(
    'Membership / Manage Plans',
    'Membership Plan Management',
    'Configure plan features, pricing, and benefits for Royal, Classic, and Standard memberships.'
);
?>

    <div class="main-content">
        <div class="content membership-content-shell">
            <?php foreach ($messages as $msg): ?>
                <div class="message"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>

            <div class="tabs-container membership-tabs-container">
                <div class="tab-header">
                    <?php foreach ($membership_config as $type => $cfg): ?>
                        <button
                            type="button"
                            class="tab-btn membership-tab-btn <?php echo $active_tab === $type ? 'active' : ''; ?>"
                            data-target="membership-<?php echo $type; ?>"
                        >
                            <?php echo htmlspecialchars($cfg['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($membership_config as $type => $cfg): ?>
                    <div id="membership-<?php echo $type; ?>" class="tab-content membership-tab-content <?php echo $active_tab === $type ? 'active' : ''; ?>">
                        <div class="tab-top">
                            <h3><?php echo htmlspecialchars($cfg['title']); ?> Features</h3>
                            <button type="button" class="add-service-btn" onclick="openMembershipAddModal('<?php echo $type; ?>')">
                                <i class="fas fa-plus"></i> Add Feature
                            </button>
                        </div>

                        <p class="membership-tab-intro"><?php echo htmlspecialchars($cfg['description']); ?></p>

                        <div class="service-table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Plan</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($membership_rows[$type])): ?>
                                        <?php
                                            $pager = $membership_pagination[$type];
                                            $row_number = (($pager['current_page'] - 1) * $records_per_page) + 1;
                                        ?>
                                        <?php foreach ($membership_rows[$type] as $row): ?>
                                            <?php
                                                $record_id = (int) $row[$cfg['id_col']];
                                                $record_plan = $row[$cfg['plan_col']];
                                                $record_desc = $row[$cfg['desc_col']];
                                                $record_price = (int) $row[$cfg['price_col']];
                                            ?>
                                            <tr>
                                                <td><?php echo $row_number++; ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($record_plan)); ?></td>
                                                <td><?php echo htmlspecialchars($record_desc); ?></td>
                                                <td>₹<?php echo number_format($record_price, 0); ?></td>
                                                <td>
                                                    <div class="services-buttons">
                                                        <button
                                                            type="button"
                                                            class="service-update"
                                                            onclick='openMembershipEditModal(<?php echo json_encode($type); ?>, <?php echo $record_id; ?>, <?php echo json_encode($record_plan); ?>, <?php echo json_encode($record_desc); ?>, <?php echo $record_price; ?>)'
                                                            title="Edit Feature"
                                                            aria-label="Edit feature"
                                                        >
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <form method="post" class="inline-action-form">
                                                            <input type="hidden" name="membership_action" value="delete">
                                                            <input type="hidden" name="membership_type" value="<?php echo htmlspecialchars($type); ?>">
                                                            <input type="hidden" name="membership_id" value="<?php echo $record_id; ?>">
                                                            <input type="hidden" name="<?php echo htmlspecialchars($membership_pagination[$type]['page_param']); ?>" value="<?php echo (int) $membership_pagination[$type]['current_page']; ?>">
                                                            <button
                                                                type="submit"
                                                                class="service-delete"
                                                                title="Delete Feature"
                                                                aria-label="Delete feature"
                                                                onclick="return confirm('Are you sure you want to delete this membership feature?');"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="membership-empty-state">No membership features found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                            $pager = $membership_pagination[$type];
                            $extra_params = ['tab' => $type];
                            foreach ($membership_pagination as $pType => $pMeta) {
                                $param_name = $pMeta['page_param'];
                                if (isset($_GET[$param_name])) {
                                    $extra_params[$param_name] = (int) $_GET[$param_name];
                                }
                            }
                            echo renderPagination(
                                $pager['total_records'],
                                $pager['current_page'],
                                $records_per_page,
                                'membership_manage.php',
                                $extra_params,
                                $pager['page_param']
                            );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="membership_add_modal">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="membership_add_modal_title">Add Membership Feature</h3>
                <button type="button" class="close-modal" onclick="closeModal('membership_add_modal')">&times;</button>
            </div>
            <form class="modal-form" method="post">
                <input type="hidden" name="membership_action" value="add">
                <input type="hidden" name="membership_type" id="membership_add_type" value="royal">

                <label for="membership_add_plan">Plan Type:</label>
                <select id="membership_add_plan" name="membership_plan" required>
                    <option value="yearly">Yearly Plan</option>
                    <option value="monthly">Monthly Plan</option>
                </select>

                <label for="membership_add_desc">Feature Description:</label>
                <input type="text" id="membership_add_desc" name="membership_desc" placeholder="Enter feature description" required>

                <label for="membership_add_price">Price:</label>
                <input type="number" id="membership_add_price" name="membership_price" min="0" step="1" placeholder="Enter price" required>

                <button type="submit" class="modal-submit-btn">Add Feature</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="membership_edit_modal">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="membership_edit_modal_title">Edit Membership Feature</h3>
                <button type="button" class="close-modal" onclick="closeModal('membership_edit_modal')">&times;</button>
            </div>
            <form class="modal-form" method="post">
                <input type="hidden" name="membership_action" value="update">
                <input type="hidden" name="membership_type" id="membership_edit_type" value="royal">
                <input type="hidden" name="membership_id" id="membership_edit_id" value="0">

                <label for="membership_edit_plan">Plan Type:</label>
                <select id="membership_edit_plan" name="membership_plan" required>
                    <option value="yearly">Yearly Plan</option>
                    <option value="monthly">Monthly Plan</option>
                </select>

                <label for="membership_edit_desc">Feature Description:</label>
                <input type="text" id="membership_edit_desc" name="membership_desc" required>

                <label for="membership_edit_price">Price:</label>
                <input type="number" id="membership_edit_price" name="membership_price" min="0" step="1" required>

                <button type="submit" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        const membershipTitles = {
            royal: 'Royal Pass',
            classic: 'Classic Pass',
            standard: 'Standard Pass'
        };

        function openMembershipTab(tabType) {
            const tabId = `membership-${tabType}`;

            document.querySelectorAll('.membership-tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.target === tabId);
            });

            document.querySelectorAll('.membership-tab-content').forEach(content => {
                content.classList.toggle('active', content.id === tabId);
            });
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function openMembershipAddModal(type) {
            document.getElementById('membership_add_type').value = type;
            document.getElementById('membership_add_modal_title').textContent = `Add ${membershipTitles[type]} Feature`;
            document.getElementById('membership_add_plan').value = 'yearly';
            document.getElementById('membership_add_desc').value = '';
            document.getElementById('membership_add_price').value = '';

            openMembershipTab(type);
            openModal('membership_add_modal');
        }

        function openMembershipEditModal(type, id, plan, description, price) {
            document.getElementById('membership_edit_type').value = type;
            document.getElementById('membership_edit_id').value = id;
            document.getElementById('membership_edit_modal_title').textContent = `Edit ${membershipTitles[type]} Feature`;
            document.getElementById('membership_edit_plan').value = plan;
            document.getElementById('membership_edit_desc').value = description;
            document.getElementById('membership_edit_price').value = price;

            openMembershipTab(type);
            openModal('membership_edit_modal');
        }

        document.querySelectorAll('.membership-tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                const tabTarget = this.dataset.target.replace('membership-', '');
                openMembershipTab(tabTarget);
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            openMembershipTab(<?php echo json_encode($active_tab); ?>);
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
