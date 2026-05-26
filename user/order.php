<?php
include 'header.php';
include 'connect.php';
require_once '../admin/filter_helper.php';
require_once '../admin/pagination_helper.php';

$user_id = (int) $_SESSION['user_id'];

// Pagination (groups orders by date+time so an order stays together)
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// Filter Configuration
$filterConfig = [
    'search' => ['col' => 's_name', 'type' => 'like'],
    'status' => ['col' => 's_status', 'type' => 'equals'],
    'start_date' => ['col' => 's_date', 'type' => 'date_start'],
    'end_date' => ['col' => 's_date', 'type' => 'date_end']
];

$whereClause = buildSimpleWhere($con, $filterConfig, " AND ");

// 1) Find the order groups for this page (each group = one checkout time)
$total_groups = 0;
$countGroupsResult = mysqli_query(
    $con,
    "SELECT COUNT(*) AS total FROM (
        SELECT s_date, s_time
        FROM product_sales
        WHERE id = {$user_id} $whereClause
        GROUP BY s_date, s_time
    ) AS grouped_orders"
);
if ($countGroupsResult) {
    $countRow = mysqli_fetch_assoc($countGroupsResult);
    $total_groups = (int) ($countRow['total'] ?? 0);
}

$total_pages = max(1, (int) ceil($total_groups / $records_per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $records_per_page;

$groupsResult = mysqli_query(
    $con,
    "SELECT s_date, s_time
     FROM product_sales
     WHERE id = {$user_id} $whereClause
     GROUP BY s_date, s_time
     ORDER BY s_date DESC, s_time DESC
     LIMIT {$offset}, {$records_per_page}"
);

if (!$groupsResult) {
    die('Database query failed: ' . mysqli_error($con));
}

$groupKeys = [];
while ($g = mysqli_fetch_assoc($groupsResult)) {
    $groupKeys[] = trim((string) ($g['s_date'] ?? '')) . '|' . trim((string) ($g['s_time'] ?? ''));
}

$salesResult = null;
if (!empty($groupKeys)) {
    // Build a safe OR chain for the page's group keys (values come from DB, still escaped defensively)
    $groupWhere = [];
    foreach ($groupKeys as $key) {
        $parts = explode('|', $key, 2);
        $d = mysqli_real_escape_string($con, $parts[0] ?? '');
        $t = mysqli_real_escape_string($con, $parts[1] ?? '');
        $groupWhere[] = "(s_date = '{$d}' AND s_time = '{$t}')";
    }
    $groupWhereSql = implode(' OR ', $groupWhere);

    $salesResult = mysqli_query(
        $con,
        "SELECT * FROM product_sales
         WHERE id = {$user_id} $whereClause AND ({$groupWhereSql})
         ORDER BY s_date DESC, s_time DESC, s_id DESC"
    );
}

$paymentBySaleId = [];
$paymentByDateTime = [];
$paymentsResult = mysqli_query($con, "SELECT * FROM payment WHERE id = {$user_id} ORDER BY pay_id DESC");
if ($paymentsResult) {
    while ($paymentRow = mysqli_fetch_assoc($paymentsResult)) {
        $saleId = (int) $paymentRow['s_id'];
        if ($saleId > 0 && !isset($paymentBySaleId[$saleId])) {
            $paymentBySaleId[$saleId] = $paymentRow;
        }

        $dateTimeKey = trim($paymentRow['p_date']) . '|' . trim($paymentRow['p_time']);
        if (!isset($paymentByDateTime[$dateTimeKey])) {
            $paymentByDateTime[$dateTimeKey] = $paymentRow;
        }
    }
}

$orderGroups = [];
if ($salesResult) {
    while ($saleRow = mysqli_fetch_assoc($salesResult)) {
    $saleId = (int) $saleRow['s_id'];
    $history = [];
    $historyResult = mysqli_query($con, "SELECT * FROM order_status_updates WHERE s_id = {$saleId} ORDER BY id ASC");
    if ($historyResult) {
        while ($statusRow = mysqli_fetch_assoc($historyResult)) {
            $history[strtolower($statusRow['status'])] = $statusRow;
        }
    }

    $currentStatus = strtolower(trim($saleRow['s_status']));
    if (!isset($history[$currentStatus])) {
        $history[$currentStatus] = [
            'update_date' => $saleRow['s_date'],
            'update_time' => $saleRow['s_time'],
            'status' => $currentStatus
        ];
    }

    $groupKey = trim($saleRow['s_date']) . '|' . trim($saleRow['s_time']);
    $payment = null;
    if (isset($paymentBySaleId[$saleId])) {
        $payment = $paymentBySaleId[$saleId];
    } elseif (isset($paymentByDateTime[$groupKey])) {
        $payment = $paymentByDateTime[$groupKey];
    }

    $saleRow['history'] = $history;
    $saleRow['payment_method'] = $payment ? strtolower(trim($payment['p_method'])) : 'cod';
    $saleRow['payment_status'] = $payment ? strtolower(trim($payment['p_status'])) : 'pending';
    $saleRow['payment_intent'] = $payment && !empty($payment['stripe_payment_intent_id']) ? $payment['stripe_payment_intent_id'] : '';
    $orderGroups[$groupKey][] = $saleRow;
    }
}

$productLookup = [];
$productQuery = mysqli_query($con, "SELECT p_id, p_name, p_size FROM products");
if ($productQuery) {
    while ($productRow = mysqli_fetch_assoc($productQuery)) {
        $key = strtolower(trim($productRow['p_name'])) . '|' . strtolower(trim($productRow['p_size']));
        if (!isset($productLookup[$key])) {
            $productLookup[$key] = (int) $productRow['p_id'];
        }
    }
}

function getTrackingSteps($row)
{
    $history = $row['history'];
    $status = strtolower($row['s_status']);
    $paymentStatus = strtolower($row['payment_status']);
    $paymentMethod = strtolower($row['payment_method']);

    if ($status === 'refunded' || isset($history['refunded']) || $paymentStatus === 'refunded') {
        return ['pending', 'confirmed', 'processing', 'cancelled', 'refunded'];
    }
    if ($status === 'cancelled' || isset($history['cancelled'])) {
        if (in_array($paymentMethod, ['stripe', 'wallet'], true)) {
            return ['pending', 'confirmed', 'processing', 'cancelled', 'refunded'];
        }
        return ['pending', 'confirmed', 'cancelled'];
    }
    return ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
}

function getStepIcon($step)
{
    switch ($step) {
        case 'pending':
            return 'fa-clock';
        case 'confirmed':
            return 'fa-check';
        case 'processing':
            return 'fa-cog';
        case 'shipped':
            return 'fa-truck';
        case 'delivered':
            return 'fa-home';
        case 'cancelled':
            return 'fa-ban';
        case 'refunded':
            return 'fa-wallet';
        default:
            return 'fa-circle';
    }
}
?>

<style>
    .order-tracking-container {
        padding: 20px 0;
        margin-top: 20px;
        border-top: 1px solid #eee;
    }

    .tracking-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 10px;
    }

    .tracking-steps::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e0e0e0;
        z-index: 1;
    }

    .step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }

    .step-icon {
        width: 30px;
        height: 30px;
        background: #e0e0e0;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 12px;
        border: 4px solid white;
    }

    .step.active .step-icon {
        background: var(--brand);
        box-shadow: 0 0 10px rgba(203, 185, 15, 0.4);
    }

    .step-text {
        font-size: 11px;
        font-weight: 600;
        color: #999;
        text-transform: capitalize;
    }

    .step.active .step-text {
        color: var(--brand);
    }

    .step-time {
        font-size: 9px;
        color: #bbb;
        display: block;
        margin-top: 2px;
    }

    .step.active .step-time {
        color: #666;
    }

    .order-card-modern {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card-top {
        display: flex;
        padding: 20px;
    }

    .order-img-container {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eee;
        flex-shrink: 0;
    }

    .order-info-container {
        flex: 1;
        padding-left: 20px;
    }

    .order-actions-bar {
        background: #fcfcfc;
        padding: 15px 20px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 992px) {
        .card-top {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px;
        }

        .order-img-container {
            margin-bottom: 15px;
            width: 80px;
            height: 80px;
        }

        .order-info-container {
            padding-left: 0;
            width: 100%;
        }

        /* Vertical Stepper for Mobile/Tablet */
        .tracking-steps {
            flex-direction: column;
            align-items: flex-start;
            padding-left: 10px;
            margin-top: 10px;
        }

        .tracking-steps::before {
            top: 0;
            left: 24px;
            width: 2px;
            height: 100%;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            margin-bottom: 25px;
            width: 100%;
            flex: none;
            min-height: 40px;
            position: relative;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-icon {
            margin: 0;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            position: relative;
            z-index: 3;
        }

        .step-text {
            margin: 0;
            font-size: 12px;
            width: auto;
        }

        .step-time {
            margin: 0;
            margin-left: auto;
            white-space: nowrap;
        }

        /* Actions Bar Stacked */
        .order-actions-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
        }

        .order-actions-bar>div:first-child {
            width: 100%;
        }

        .order-actions-bar>div:last-child {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-start;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }

        .order-actions-bar .order-action-btn {
            flex: 1;
            min-width: 140px;
            justify-content: center;
        }
    }
</style>

<main class="content">
    <section class="order-container">
        <div class="header-with-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <h1 style="margin-bottom:0;">Your Product Orders</h1>
            <a href="../eshop.php" class="app_more" style="margin-top:0;"><i class="fas fa-shopping-basket"></i> Continue Shopping</a>
        </div>

        <div class="user-filter-section" style="margin-bottom: 30px;">
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
                    'type' => 'date',
                    'name' => 'start_date',
                    'label' => 'From Date',
                    'value' => $_GET['start_date'] ?? ''
                ],
                [
                    'type' => 'date',
                    'name' => 'end_date',
                    'label' => 'To Date',
                    'value' => $_GET['end_date'] ?? ''
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'options' => [
                        '' => 'All Status',
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded'
                    ],
                    'value' => $_GET['status'] ?? ''
                ]
            ];
            renderFilters($filters);
            ?>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div style="background:#e6f4ea;color:#1e8e3e;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orderGroups)): ?>
            <div style="padding:60px;text-align:center;background:white;border-radius:14px;border:2px dashed rgba(203,185,15,0.3);margin-top:20px;">
                <i class="fas fa-box-open" style="font-size:50px;color:var(--brand);margin-bottom:20px;"></i>
                <h3>No Orders Yet</h3>
                <p style="color:#777;margin-bottom:30px;">You haven't placed any orders yet.</p>
                <a href="../eshop.php" class="app_more" style="display:inline-block;margin-top:0;"><i class="fas fa-shopping-cart"></i> Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders">
                <?php foreach ($orderGroups as $groupKey => $orders): ?>
                    <?php $groupDate = $orders[0]['s_date'];
                    $groupTime = $orders[0]['s_time']; ?>
                    <div class="order-group" style="margin-bottom:50px;">
                        <h3 style="font-size:16px;color:var(--bg1);border-bottom:1px solid rgba(0,0,0,0.05);padding-bottom:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;">
                            <span style="background:var(--brand);width:8px;height:8px;border-radius:50%;"></span>
                            Ordered on <?php echo date('d M Y, h:i A', strtotime($groupDate . ' ' . $groupTime)); ?>
                        </h3>

                        <div class="order-items-grid">
                            <?php foreach ($orders as $row): ?>
                                <?php
                                $lookupKey = strtolower(trim($row['s_name'])) . '|' . strtolower(trim($row['s_size']));
                                $productUrl = isset($productLookup[$lookupKey]) ? "../product_display.php?id=" . $productLookup[$lookupKey] : '';
                                $currentStatus = strtolower(trim($row['s_status']));
                                $paymentMethod = strtolower(trim($row['payment_method']));
                                $paymentStatus = strtolower(trim($row['payment_status']));
                                $canCancelOrder = !in_array($currentStatus, ['shipped', 'delivered', 'cancelled', 'refunded'], true);
                                $steps = getTrackingSteps($row);
                                $originalTotal = (float) $row['s_price'] * (int) $row['s_quantity'];
                                $finalTotal = (float) $row['s_total'];

                                if ($currentStatus === 'delivered') {
                                    $statusBg = '#e6f4ea';
                                    $statusColor = '#1e8e3e';
                                    $statusIcon = 'fa-check-circle';
                                } elseif ($currentStatus === 'cancelled' && in_array($paymentMethod, ['stripe', 'wallet'], true) && $paymentStatus !== 'refunded') {
                                    $statusBg = '#fef7e0';
                                    $statusColor = '#b06000';
                                    $statusIcon = 'fa-hourglass-half';
                                } elseif ($currentStatus === 'cancelled' || $currentStatus === 'refunded') {
                                    $statusBg = '#fce8e6';
                                    $statusColor = '#d93025';
                                    $statusIcon = $currentStatus === 'refunded' ? 'fa-wallet' : 'fa-times-circle';
                                } else {
                                    $statusBg = '#fef9c3';
                                    $statusColor = '#854d0e';
                                    $statusIcon = 'fa-info-circle';
                                }

                                $statusLabel = ucfirst($currentStatus);
                                if ($currentStatus === 'cancelled' && in_array($paymentMethod, ['stripe', 'wallet'], true) && $paymentStatus !== 'refunded') {
                                    $statusLabel = 'Refund In Progress';
                                }
                                ?>
                                <div class="order-card-modern">
                                    <div class="card-top">
                                        <div class="order-img-container">
                                            <img src="../upload_product_photos/<?php echo htmlspecialchars($row['s_img']); ?>" alt="Product" style="width:100%;height:100%;object-fit:cover;">
                                        </div>

                                        <div class="order-info-container">
                                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                                                <div>
                                                    <?php if ($productUrl): ?>
                                                        <h2 style="font-size:18px;margin:0;"><a href="<?php echo htmlspecialchars($productUrl); ?>" class="order-product-link"><?php echo htmlspecialchars($row['s_name']); ?></a></h2>
                                                    <?php else: ?>
                                                        <h2 style="font-size:18px;color:var(--bg1);margin:0;"><?php echo htmlspecialchars($row['s_name']); ?></h2>
                                                    <?php endif; ?>
                                                    <?php if ($originalTotal > $finalTotal): ?>
                                                        <div style="font-size:12px;color:#777;text-decoration:line-through;">₹<?php echo number_format($originalTotal, 2); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <span style="font-weight:700;color:var(--brand);font-size:18px;">₹<?php echo number_format($finalTotal, 2); ?></span>
                                            </div>

                                            <div style="color:#666;font-size:14px;margin-bottom:8px;">
                                                <span style="margin-right:15px;"><i class="fas fa-tag"></i> Size: <?php echo htmlspecialchars($row['s_size']); ?></span>
                                                <span style="margin-right:15px;"><i class="fas fa-boxes"></i> Qty: <?php echo (int) $row['s_quantity']; ?></span>
                                                <span><i class="fas fa-credit-card"></i> <?php echo strtoupper($paymentMethod); ?> / <?php echo strtoupper($paymentStatus); ?></span>
                                            </div>

                                            <div class="order-tracking-container">
                                                <div class="tracking-steps">
                                                    <?php foreach ($steps as $step): ?>
                                                        <?php
                                                        $historyKey = strtolower($step);
                                                        $isActive = isset($row['history'][$historyKey]) || $currentStatus === $historyKey || ($historyKey === 'refunded' && $paymentStatus === 'refunded');
                                                        $displayTime = '';
                                                        if (isset($row['history'][$historyKey])) {
                                                            $displayTime = date(
                                                                'd/m h:i A',
                                                                strtotime($row['history'][$historyKey]['update_date'] . ' ' . $row['history'][$historyKey]['update_time'])
                                                            );
                                                        }
                                                        ?>
                                                        <div class="step <?php echo $isActive ? 'active' : ''; ?>">
                                                            <div class="step-icon"><i class="fas <?php echo getStepIcon($step); ?>"></i></div>
                                                            <div class="step-text"><?php echo $step; ?></div>
                                                            <div class="step-time"><?php echo $displayTime; ?></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="order-actions-bar">
                                        <div>
                                            <span style="background:<?php echo $statusBg; ?>;color:<?php echo $statusColor; ?>;font-size:12px;padding:6px 12px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;font-weight:600;">
                                                <i class="fas <?php echo $statusIcon; ?>"></i> Status: <?php echo $statusLabel; ?>
                                            </span>
                                        </div>
                                        <div style="display:flex;gap:10px;">
                                            <a href="invoice.php?time=<?php echo urlencode($row['s_time']); ?>" class="order-action-btn order-action-primary" style="padding:8px 15px;font-size:13px;">
                                                <i class="fas fa-file-invoice"></i> Download Invoice
                                            </a>

                                            <?php if ($canCancelOrder): ?>
                                                <form action="cancel_order.php" method="get" style="margin:0;">
                                                    <input type="hidden" name="id" value="<?php echo (int) $row['s_id']; ?>">
                                                    <button type="submit" class="order-action-btn order-action-danger" style="padding:8px 15px;font-size:13px;" onclick="return confirm('Cancel this order?')">
                                                        <i class="fas fa-ban"></i> Cancel Order
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            $params = $_GET;
            unset($params['page']);
            echo renderPagination($total_groups, $current_page, $records_per_page, 'order.php', $params);
            ?>
        <?php endif; ?>
    </section>
</main>
