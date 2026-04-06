<?php
include('header.php');
include('connect.php');
require_once('page_header_helper.php');
require_once('filter_helper.php');

mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `membership_plans` (
      `mp_id` int(11) NOT NULL AUTO_INCREMENT,
      `pass_key` varchar(20) NOT NULL,
      `display_name` varchar(150) NOT NULL,
      `billing_plan` varchar(20) NOT NULL,
      `price` int(11) NOT NULL DEFAULT 0,
      `features_json` longtext NOT NULL,
      `is_featured` tinyint(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (`mp_id`),
      UNIQUE KEY `uq_pass_billing` (`pass_key`,`billing_plan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$featured_col = mysqli_query($con, "SHOW COLUMNS FROM membership_plans LIKE 'is_featured'");
if ($featured_col && mysqli_num_rows($featured_col) === 0) {
    mysqli_query(
        $con,
        'ALTER TABLE membership_plans ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER features_json'
    );
}

$membership_config = [
    'royal' => [
        'title' => 'Royal Pass',
        'description' => 'Premium membership with exclusive offers and priority benefits.',
    ],
    'classic' => [
        'title' => 'Classic Pass',
        'description' => 'Balanced membership with solid discounts and recurring perks.',
    ],
    'standard' => [
        'title' => 'Standard Pass',
        'description' => 'Entry membership with essential savings and member-only access.',
    ],
];

$messages = [];
$active_tab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'royal';
if (!isset($membership_config[$active_tab])) {
    $active_tab = 'royal';
}

// Filter Configuration
$filterConfig = [
    'search' => ['col' => 'display_name', 'type' => 'like']
];
$filterWhere = buildSimpleWhere($con, $filterConfig, " AND ");

function membership_collect_features_from_post(): array
{
    if (!isset($_POST['membership_features']) || !is_array($_POST['membership_features'])) {
        return [];
    }
    $out = [];
    foreach ($_POST['membership_features'] as $line) {
        $t = trim((string) $line);
        if ($t !== '') {
            $out[] = $t;
        }
    }
    return array_values($out);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_action'], $_POST['pass_key'])) {
    $action = strtolower(trim($_POST['membership_action']));
    $pass_key = strtolower(trim($_POST['pass_key']));

    if (!isset($membership_config[$pass_key])) {
        $messages[] = 'Invalid membership tier selected.';
    } else {
        $active_tab = $pass_key;

        if ($action === 'delete') {
            $mp_id = isset($_POST['mp_id']) ? (int) $_POST['mp_id'] : 0;
            if ($mp_id <= 0) {
                $messages[] = 'Invalid plan selected for deletion.';
            } else {
                $stmt = mysqli_prepare($con, 'DELETE FROM membership_plans WHERE mp_id = ? AND pass_key = ?');
                mysqli_stmt_bind_param($stmt, 'is', $mp_id, $pass_key);
                if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                    mysqli_stmt_close($stmt);
                    header('Location: membership_manage.php?tab=' . urlencode($pass_key) . '&toast=success&msg=' . urlencode('Membership plan removed.'));
                    exit();
                }
                mysqli_stmt_close($stmt);
                $messages[] = 'Could not delete that plan.';
            }
        } else {
            $display_name = isset($_POST['display_name']) ? trim($_POST['display_name']) : '';
            $billing_raw = isset($_POST['billing_plan']) ? strtolower(trim($_POST['billing_plan'])) : '';
            $price_raw = isset($_POST['price']) ? trim($_POST['price']) : '';
            $allowed_billing = ['yearly', 'monthly'];
            $billing_plan = in_array($billing_raw, $allowed_billing, true) ? $billing_raw : '';
            $price = is_numeric($price_raw) ? (int) $price_raw : -1;
            $features = membership_collect_features_from_post();
            $is_featured = isset($_POST['is_featured']) && $_POST['is_featured'] === '1' ? 1 : 0;

            if ($display_name === '' || $billing_plan === '' || $price < 0) {
                $messages[] = 'Please enter name, billing plan, and a valid price.';
            } elseif ($action === 'add') {
                $features_json = json_encode($features, JSON_UNESCAPED_UNICODE);
                if ($features_json === false) {
                    $features_json = '[]';
                }
                $stmt = mysqli_prepare(
                    $con,
                    'INSERT INTO membership_plans (pass_key, display_name, billing_plan, price, features_json, is_featured) VALUES (?, ?, ?, ?, ?, ?)'
                );
                mysqli_stmt_bind_param($stmt, 'sssisi', $pass_key, $display_name, $billing_plan, $price, $features_json, $is_featured);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: membership_manage.php?tab=' . urlencode($pass_key) . '&toast=success&msg=' . urlencode('Membership plan created.'));
                    exit();
                }
                if (mysqli_errno($con) === 1062) {
                    $messages[] = 'A plan for this billing period already exists. Edit it instead.';
                } else {
                    $messages[] = 'Could not create plan: ' . mysqli_error($con);
                }
                mysqli_stmt_close($stmt);
            } elseif ($action === 'update') {
                $mp_id = isset($_POST['mp_id']) ? (int) $_POST['mp_id'] : 0;
                if ($mp_id <= 0) {
                    $messages[] = 'Invalid plan for update.';
                } else {
                    $dup = mysqli_prepare(
                        $con,
                        'SELECT mp_id FROM membership_plans WHERE pass_key = ? AND billing_plan = ? AND mp_id <> ? LIMIT 1'
                    );
                    mysqli_stmt_bind_param($dup, 'ssi', $pass_key, $billing_plan, $mp_id);
                    mysqli_stmt_execute($dup);
                    $dup_res = mysqli_stmt_get_result($dup);
                    if ($dup_res && mysqli_fetch_assoc($dup_res)) {
                        mysqli_stmt_close($dup);
                        $messages[] = 'Another plan already uses this billing period for this tier.';
                    } else {
                        mysqli_stmt_close($dup);
                        $features_json = json_encode($features, JSON_UNESCAPED_UNICODE);
                        if ($features_json === false) {
                            $features_json = '[]';
                        }
                        $stmt = mysqli_prepare(
                            $con,
                            'UPDATE membership_plans SET display_name = ?, billing_plan = ?, price = ?, features_json = ?, is_featured = ? WHERE mp_id = ? AND pass_key = ?'
                        );
                        mysqli_stmt_bind_param($stmt, 'ssisiis', $display_name, $billing_plan, $price, $features_json, $is_featured, $mp_id, $pass_key);
                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            header('Location: membership_manage.php?tab=' . urlencode($pass_key) . '&toast=success&msg=' . urlencode('Membership plan updated.'));
                            exit();
                        }
                        $messages[] = 'Could not update plan.';
                        mysqli_stmt_close($stmt);
                    }
                }
            } else {
                $messages[] = 'Invalid membership action.';
            }
        }
    }
}

$membership_rows = [];
foreach (array_keys($membership_config) as $pk) {
    $membership_rows[$pk] = [];
    $query = "SELECT mp_id, pass_key, display_name, billing_plan, price, features_json, is_featured FROM membership_plans WHERE pass_key = ? $filterWhere ORDER BY billing_plan DESC";
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $pk);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $membership_rows[$pk][] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

function membership_features_preview(string $json, int $max = 3): string
{
    $arr = json_decode($json, true);
    if (!is_array($arr)) {
        return '—';
    }
    $arr = array_values(array_filter(array_map('trim', $arr)));
    if ($arr === []) {
        return '—';
    }
    $shown = array_slice($arr, 0, $max);
    $text = implode('; ', $shown);
    if (count($arr) > $max) {
        $text .= ' …';
    }
    return $text;
}
?>

<?php
renderAdminPageIntro(
    'Membership / Manage Plans',
    'Membership Plan Management',
    'Each row is one sellable plan. Use Highlight to mark a most-popular plan on the public membership page.'
);
?>

<div class="main-content">
    <div class="content" style="background: transparent; box-shadow: none; padding: 0;">
        <?php
        $filters = [
            [
                'type' => 'text',
                'name' => 'search',
                'placeholder' => 'Search plan name...',
                'value' => $_GET['search'] ?? '',
                'label' => 'Search Plans'
            ]
        ];
        renderFilters($filters, '', ['tab' => $active_tab]);
        ?>
    </div>
    <div class="content membership-content-shell">
        <?php foreach ($messages as $msg): ?>
            <div class="message"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>

        <div class="tabs-container membership-tabs-container">
            <div class="tab-header">
                <?php
                $currentSearch = $_GET['search'] ?? '';
                foreach ($membership_config as $type => $cfg):
                    $tabUrl = "membership_manage.php?tab=" . urlencode($type);
                    if ($currentSearch !== '') {
                        $tabUrl .= "&search=" . urlencode($currentSearch);
                    }
                ?>
                    <a
                        href="<?php echo $tabUrl; ?>"
                        class="tab-btn membership-tab-btn <?php echo $active_tab === $type ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cfg['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($membership_config as $type => $cfg): ?>
                <div id="membership-<?php echo $type; ?>" class="tab-content membership-tab-content <?php echo $active_tab === $type ? 'active' : ''; ?>">
                    <div class="tab-top">
                        <h3><?php echo htmlspecialchars($cfg['title']); ?> plans</h3>
                        <button type="button" class="add-service-btn" onclick="openMembershipAddModal('<?php echo htmlspecialchars($type); ?>')">
                            <i class="fas fa-plus"></i> Add plan
                        </button>
                    </div>

                    <p class="membership-tab-intro"><?php echo htmlspecialchars($cfg['description']); ?></p>

                    <div class="service-table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Plan</th>
                                    <th>Price</th>
                                    <th>Features (preview)</th>
                                    <th>Highlight</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($membership_rows[$type])): ?>
                                    <?php $row_number = 1; ?>
                                    <?php foreach ($membership_rows[$type] as $row): ?>
                                        <?php
                                        $record_id = (int) $row['mp_id'];
                                        $record_name = $row['display_name'];
                                        $record_plan = $row['billing_plan'];
                                        $record_price = (int) $row['price'];
                                        $record_featured = (int) ($row['is_featured'] ?? 0) === 1;
                                        $edit_payload = [
                                            'id' => $record_id,
                                            'pass_key' => $type,
                                            'display_name' => $record_name,
                                            'billing_plan' => $record_plan,
                                            'price' => $record_price,
                                            'is_featured' => $record_featured,
                                            'features' => json_decode($row['features_json'], true),
                                        ];
                                        if (!is_array($edit_payload['features'])) {
                                            $edit_payload['features'] = [];
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $row_number++; ?></td>
                                            <td><?php echo htmlspecialchars($record_name); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($record_plan)); ?></td>
                                            <td>₹<?php echo number_format($record_price, 0); ?></td>
                                            <td class="membership-features-preview"><?php echo htmlspecialchars(membership_features_preview($row['features_json'])); ?></td>
                                            <td><?php echo $record_featured ? '<span title="Shown as highlighted on the public membership page"><i class="fas fa-star" style="color:#cbb90f;"></i> Yes</span>' : '—'; ?></td>
                                            <td>
                                                <div class="services-buttons">
                                                    <button
                                                        type="button"
                                                        class="service-view"
                                                        onclick="openMembershipViewModal(<?php echo htmlspecialchars(json_encode($edit_payload), ENT_QUOTES, 'UTF-8'); ?>)"
                                                        title="View details"
                                                        aria-label="View details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="service-update"
                                                        onclick="openMembershipEditModal(<?php echo htmlspecialchars(json_encode($edit_payload), ENT_QUOTES, 'UTF-8'); ?>)"
                                                        title="Edit plan"
                                                        aria-label="Edit plan">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <form method="post" class="inline-action-form">
                                                        <input type="hidden" name="membership_action" value="delete">
                                                        <input type="hidden" name="pass_key" value="<?php echo htmlspecialchars($type); ?>">
                                                        <input type="hidden" name="mp_id" value="<?php echo $record_id; ?>">
                                                        <button
                                                            type="submit"
                                                            class="service-delete"
                                                            title="Delete plan"
                                                            aria-label="Delete plan"
                                                            onclick="return confirm('Delete this entire plan (all features and price)?');">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="membership-empty-state">No plans yet. Add a yearly or monthly package, or run <code>admin/setup_membership_plans_table.php</code> to import legacy rows.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="membership_add_modal">
    <div class="modal-box membership-plan-modal">
        <div class="modal-header">
            <h3 id="membership_add_modal_title">Add membership plan</h3>
            <button type="button" class="close-modal" onclick="closeModal('membership_add_modal')">&times;</button>
        </div>
        <form class="modal-form" method="post" id="membership_add_form">
            <input type="hidden" name="membership_action" value="add">
            <input type="hidden" name="pass_key" id="membership_add_pass_key" value="royal">

            <label for="membership_add_name">Name</label>
            <input type="text" id="membership_add_name" name="display_name" placeholder="e.g. Royal Pass" required>

            <label for="membership_add_plan">Billing plan</label>
            <select id="membership_add_plan" name="billing_plan" required>
                <option value="yearly">Yearly</option>
                <option value="monthly">Monthly</option>
            </select>

            <label for="membership_add_price">Price (₹)</label>
            <input type="number" id="membership_add_price" name="price" min="0" step="1" placeholder="0" required>

            <label class="membership-checkbox-label">
                <input type="checkbox" id="membership_add_featured" name="is_featured" value="1">
                Highlight on website (e.g. most popular plan)
            </label>

            <div class="membership-features-block">
                <div class="membership-features-head">
                    <label>Features</label>
                    <button type="button" class="membership-add-feature-btn" onclick="addMembershipFeatureRow('membership_add_features')" aria-label="Add feature line">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <p class="membership-features-hint">Add all benefit lines for this plan; they are saved together as one package.</p>
                <div id="membership_add_features" class="membership-features-list"></div>
            </div>

            <button type="submit" class="modal-submit-btn">Save plan</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="membership_edit_modal">
    <div class="modal-box membership-plan-modal">
        <div class="modal-header">
            <h3 id="membership_edit_modal_title">Edit membership plan</h3>
            <button type="button" class="close-modal" onclick="closeModal('membership_edit_modal')">&times;</button>
        </div>
        <form class="modal-form" method="post" id="membership_edit_form">
            <input type="hidden" name="membership_action" value="update">
            <input type="hidden" name="pass_key" id="membership_edit_pass_key" value="royal">
            <input type="hidden" name="mp_id" id="membership_edit_id" value="0">

            <label for="membership_edit_name">Name</label>
            <input type="text" id="membership_edit_name" name="display_name" required>

            <label for="membership_edit_plan">Billing plan</label>
            <select id="membership_edit_plan" name="billing_plan" required>
                <option value="yearly">Yearly</option>
                <option value="monthly">Monthly</option>
            </select>

            <label for="membership_edit_price">Price (₹)</label>
            <input type="number" id="membership_edit_price" name="price" min="0" step="1" required>

            <label class="membership-checkbox-label">
                <input type="checkbox" id="membership_edit_featured" name="is_featured" value="1">
                Highlight on website (e.g. most popular plan)
            </label>

            <div class="membership-features-block">
                <div class="membership-features-head">
                    <label>Features</label>
                    <button type="button" class="membership-add-feature-btn" onclick="addMembershipFeatureRow('membership_edit_features')" aria-label="Add feature line">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <p class="membership-features-hint">Add or remove lines; empty rows are ignored on save.</p>
                <div id="membership_edit_features" class="membership-features-list"></div>
            </div>

            <button type="submit" class="modal-submit-btn" style="background:var(--brand); color:var(--bg1);">Update plan</button>
        </form>
    </div>
</div>

<style>
    .membership-plan-modal.modal-box {
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .membership-features-block {
        margin-top: 12px;
    }

    .membership-features-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .membership-features-head label {
        margin: 0;
    }

    .membership-add-feature-btn {
        background: var(--brand, #cbb90f);
        color: var(--bg1, #18150d);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .membership-add-feature-btn:hover {
        filter: brightness(1.05);
    }

    .membership-features-hint {
        font-size: 12px;
        opacity: 0.85;
        margin: 0 0 8px;
    }

    .membership-features-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .membership-feature-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .membership-feature-row input {
        flex: 1;
    }

    .membership-feature-remove {
        background: transparent;
        border: 1px solid rgba(0, 0, 0, 0.15);
        color: #c0392b;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .membership-features-preview {
        max-width: 280px;
        font-size: 13px;
        line-height: 1.35;
    }

    .membership-checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        cursor: pointer;
        font-weight: 500;
    }

    .membership-checkbox-label input {
        width: auto;
        margin: 0;
    }

    .service-view {
        background: #e1f5fe;
        color: #0288d1;
        border: none;
        padding: 10px;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .service-view:hover {
        background: #0288d1;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(2, 136, 209, 0.2);
    }

    .view-feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
        border-left: 4px solid var(--brand, #cbb90f);
        color: #18150d;
        font-weight: 500;
        font-size: 0.95rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .view-label {
        color: #888;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        display: block;
        margin-bottom: 4px;
    }

    .view-value {
        font-weight: 600;
        color: #18150d;
        font-size: 1.05rem;
    }
</style>

<div class="modal-overlay" id="membership_view_modal">
    <div class="modal-box" style="max-width: 600px; background: #fff;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h3 id="membership_view_modal_title" style="color: #18150d; font-weight: 800;">Plan Details</h3>
            <button type="button" class="close-modal" onclick="closeModal('membership_view_modal')" style="color: #888;">&times;</button>
        </div>
        <div class="modal-view-body" style="padding: 24px;">
            <div class="view-item" style="margin-bottom: 20px;">
                <span class="view-label">Plan Name</span>
                <div id="view_name" class="view-value" style="font-size: 1.3rem; color: var(--brand, #cbb90f);"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <div class="view-item">
                    <span class="view-label">Billing Cycle</span>
                    <div id="view_plan" class="view-value"></div>
                </div>
                <div class="view-item">
                    <span class="view-label">Price Point</span>
                    <div id="view_price" class="view-value" style="background: #fcf8e3; display: inline-block; padding: 4px 12px; border-radius: 6px; color: #856404;"></div>
                </div>
            </div>

            <div class="view-item" style="margin-bottom: 24px;">
                <span class="view-label">Highlight Status</span>
                <div id="view_featured" class="view-value"></div>
            </div>

            <div class="view-item">
                <span class="view-label">Executive Features</span>
                <ul id="view_features" style="list-style: none; padding: 0; margin-top: 12px;"></ul>
            </div>
        </div>
        <div class="modal-footer" style="padding: 20px; text-align: right; border-top: 1px solid #eee; background: #fdfdfd; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="modal-submit-btn" style="background: #18150d; color: #fff; border-radius: 8px; padding: 10px 24px;" onclick="closeModal('membership_view_modal')">Back</button>
        </div>
    </div>
</div>

<script>
    const membershipTitles = <?php
                                $ts = [];
                                foreach ($membership_config as $k => $c) {
                                    $ts[$k] = $c['title'];
                                }
                                echo json_encode($ts);
                                ?>;

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    }

    function addMembershipFeatureRow(containerId, value = '') {
        const wrap = document.getElementById(containerId);
        if (!wrap) return;
        const row = document.createElement('div');
        row.className = 'membership-feature-row';
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'membership_features[]';
        input.placeholder = 'Feature description';
        input.value = value == null ? '' : String(value);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'membership-feature-remove';
        btn.title = 'Remove';
        btn.setAttribute('aria-label', 'Remove feature');
        btn.innerHTML = '&times;';
        btn.addEventListener('click', () => row.remove());
        row.appendChild(input);
        row.appendChild(btn);
        wrap.appendChild(row);
    }

    function clearMembershipFeatureRows(containerId) {
        const wrap = document.getElementById(containerId);
        if (wrap) wrap.innerHTML = '';
    }

    function openMembershipTab(tabType) {
        const tabId = `membership-${tabType}`;
        document.querySelectorAll('.membership-tab-btn').forEach(btn => {
            const isActive = (btn.href && btn.href.includes(`tab=${tabType}`)) || (btn.classList.contains('active') && btn.textContent.toLowerCase().includes(tabType));
            btn.classList.toggle('active', isActive);
        });
        document.querySelectorAll('.membership-tab-content').forEach(content => {
            content.classList.toggle('active', content.id === tabId);
        });
    }

    function openMembershipViewModal(data) {
        const passKey = data.pass_key || 'royal';
        document.getElementById('membership_view_modal_title').textContent = `Summary — ${membershipTitles[passKey] || passKey}`;
        document.getElementById('view_name').textContent = data.display_name || '';
        document.getElementById('view_plan').textContent = data.billing_plan ? data.billing_plan.charAt(0).toUpperCase() + data.billing_plan.slice(1) : '';
        document.getElementById('view_price').textContent = `₹${parseInt(data.price).toLocaleString()}`;
        document.getElementById('view_featured').innerHTML = data.is_featured ? '<i class="fas fa-star" style="color:#cbb90f;"></i> Featured Plan (Highlighted)' : 'Regular Plan';

        const featureList = document.getElementById('view_features');
        featureList.innerHTML = '';
        const feats = Array.isArray(data.features) ? data.features : [];
        if (feats.length === 0) {
            featureList.innerHTML = '<li style="color: rgba(255,255,255,0.4);">No features listed</li>';
        } else {
            feats.forEach(f => {
                const li = document.createElement('li');
                li.className = 'view-feature-item';
                li.innerHTML = `<i class="fas fa-check-circle" style="color: var(--brand); font-size: 0.9rem;"></i> <span>${f}</span>`;
                featureList.appendChild(li);
            });
        }
        openModal('membership_view_modal');
    }

    function openMembershipAddModal(passKey) {
        document.getElementById('membership_add_pass_key').value = passKey;
        document.getElementById('membership_add_modal_title').textContent = `Add plan — ${membershipTitles[passKey] || passKey}`;
        document.getElementById('membership_add_name').value = membershipTitles[passKey] || '';
        document.getElementById('membership_add_plan').value = 'yearly';
        document.getElementById('membership_add_price').value = '';
        document.getElementById('membership_add_featured').checked = false;
        clearMembershipFeatureRows('membership_add_features');
        addMembershipFeatureRow('membership_add_features', '');
        openModal('membership_add_modal');
    }

    function openMembershipEditModal(data) {
        const passKey = data.pass_key || 'royal';
        document.getElementById('membership_edit_pass_key').value = passKey;
        document.getElementById('membership_edit_id').value = data.id;
        document.getElementById('membership_edit_modal_title').textContent = `Edit plan — ${membershipTitles[passKey] || passKey}`;
        document.getElementById('membership_edit_name').value = data.display_name || '';
        document.getElementById('membership_edit_plan').value = data.billing_plan || 'yearly';
        document.getElementById('membership_edit_price').value = data.price != null ? data.price : '';
        document.getElementById('membership_edit_featured').checked = !!data.is_featured;
        clearMembershipFeatureRows('membership_edit_features');
        const feats = Array.isArray(data.features) ? data.features : [];
        if (feats.length === 0) {
            addMembershipFeatureRow('membership_edit_features', '');
        } else {
            feats.forEach(f => addMembershipFeatureRow('membership_edit_features', f));
        }
        openModal('membership_edit_modal');
    }

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