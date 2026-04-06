<?php
include('header.php');
include('connect.php');
require_once('pagination_helper.php');
require_once('page_header_helper.php');
require_once('filter_helper.php');

$filterConfig = [
    'search' => ['type' => 'search', 'cols' => ['p.p_name', 'ur.name', 'ur.username']],
    'start_date' => ['col' => 'p.p_date', 'type' => 'date_start'],
    'end_date' => ['col' => 'p.p_date', 'type' => 'date_end'],
    'status' => ['col' => 'p.p_status', 'type' => 'equals'],
    'method' => ['col' => 'p.p_method', 'type' => 'equals'],
    'category' => [
        'type' => 'custom',
        'handler' => function($con, $val) {
            if ($val === 'membership') return "p.m_id IS NOT NULL AND (p.s_id IS NULL OR p.s_id = 0)";
            if ($val === 'product') return "p.s_id IS NOT NULL AND (p.m_id IS NULL OR p.m_id = 0)";
            return "";
        }
    ]
];

$whereClause = buildSimpleWhere($con, $filterConfig);

$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$count_query = "SELECT COUNT(*) AS total FROM payment p LEFT JOIN user_reg ur ON p.id = ur.id $whereClause";
$count_result = mysqli_query($con, $count_query);
$count_row = $count_result ? mysqli_fetch_assoc($count_result) : ['total' => 0];
$total_records = (int) ($count_row['total'] ?? 0);
$offset = ($current_page - 1) * $records_per_page;

$payment_query = "
    SELECT
        p.*,
        ps.s_name,
        ps.s_price,
        ps.s_total,
        ps.s_grand_total,
        ps.s_quantity,
        mp.display_name AS membership_plan_name,
        mp.billing_plan,
        ur.name AS user_name,
        ur.username AS user_username,
        ur.email AS user_email
    FROM payment p
    LEFT JOIN product_sales ps ON p.s_id = ps.s_id
    LEFT JOIN membership_plans mp ON p.m_id = mp.mp_id
    LEFT JOIN user_reg ur ON p.id = ur.id
    $whereClause
    ORDER BY p.pay_id DESC
    LIMIT {$offset}, {$records_per_page}
";
$payment_data = mysqli_query($con, $payment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <style>
        .payment-status-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }
        .payment-status-chip.status-success {
            background: #e7f9ef;
            color: #0f8a46;
        }
        .payment-status-chip.status-pending {
            background: #fff4d8;
            color: #a46000;
        }
        .payment-status-chip.status-cancelled {
            background: #fdeaea;
            color: #c53434;
        }
        .payment-status-chip.default {
            background: #eef2f7;
            color: #334155;
        }
        .table-container table th,
        .table-container table td {
            text-align: center;
            vertical-align: middle;
        }
        .payment-view-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 16px;
        }
        .payment-view-modal.show {
            display: flex;
        }
        .payment-view-card {
            background: #fff;
            width: min(900px, 100%);
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }
        .payment-view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .payment-view-header h3 {
            margin: 0;
            color: var(--bg1);
        }
        .payment-view-close {
            border: none;
            background: #f3f4f6;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            color: #555;
            line-height: 1;
        }
        .payment-view-body {
            padding: 18px 20px 22px;
        }
        .payment-view-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .payment-view-block {
            background: #fafbfc;
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 14px;
        }
        .payment-view-block h4 {
            margin: 0 0 10px;
            color: var(--bg1);
            font-size: 15px;
        }
        .payment-info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 13px;
        }
        .payment-info-row:last-child {
            border-bottom: none;
        }
        .payment-info-row strong {
            color: #111827;
            text-align: right;
        }
        .action-dropdown-content button {
            width: 100%;
        }
        @media (max-width: 860px) {
            .payment-view-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
renderAdminPageIntro(
    'Payments',
    'Payment Management',
    'Review all product and membership transactions from a single payment ledger.'
);
?>
<div class="main-content">
    <div class="content" style="background: transparent; box-shadow: none; padding: 0;">
        <?php
        $filters = [
            [
                'type' => 'text',
                'name' => 'search',
                'placeholder' => 'Search by Customer...',
                'value' => $_GET['search'] ?? '',
                'label' => 'Search'
            ],
            [
                'type' => 'date',
                'name' => 'start_date',
                'label' => 'Start Date',
                'value' => $_GET['start_date'] ?? ''
            ],
            [
                'type' => 'date',
                'name' => 'end_date',
                'label' => 'End Date',
                'value' => $_GET['end_date'] ?? ''
            ],
            [
                'type' => 'select',
                'name' => 'method',
                'label' => 'Method',
                'options' => [
                    '' => 'All Methods',
                    'stripe' => 'Stripe',
                    'cod' => 'COD',
                    'wallet' => 'Wallet'
                ],
                'value' => $_GET['method'] ?? ''
            ],
            [
                'type' => 'select',
                'name' => 'category',
                'label' => 'Payment Type',
                'options' => [
                    '' => 'All Payments',
                    'product' => 'Products Only',
                    'membership' => 'Memberships Only'
                ],
                'value' => $_GET['category'] ?? ''
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    '' => 'All Status',
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'cancelled' => 'Cancelled'
                ],
                'value' => $_GET['status'] ?? ''
            ]
        ];
        renderFilters($filters);
        ?>
    </div>

    <div class="content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Unified Payment Transactions</h2>
            <?php if (!empty($whereClause)): ?>
                <span class="filter-indicator">
                    <i class="fas fa-filter"></i> Filters Applied: <strong><?php echo $total_records; ?></strong> matches found
                </span>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payment ID</th>
                        <th>Customer</th>
                        <th>Payment Of</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Paid On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $id_counter = $offset + 1;
                if ($payment_data && mysqli_num_rows($payment_data) > 0) {
                    while ($row = mysqli_fetch_assoc($payment_data)) {
                        $paymentFor = strtolower((string) ($row['payment_for'] ?? ''));
                        if ($paymentFor === '') {
                            $paymentFor = !empty($row['m_id']) ? 'membership' : 'product';
                        }

                        $typeLabel = $paymentFor === 'membership' ? 'Membership Payment' : 'Product Payment';
                        $amount = isset($row['p_amount']) && $row['p_amount'] !== null
                            ? (float) $row['p_amount']
                            : (float) ($row['s_grand_total'] ?? 0);

                        $description = trim((string) ($row['payment_note'] ?? ''));
                        if ($description === '') {
                            if ($paymentFor === 'membership') {
                                $planName = trim((string) ($row['membership_plan_name'] ?? 'Membership'));
                                $billing = trim((string) ($row['billing_plan'] ?? ''));
                                $description = $billing !== ''
                                    ? ucfirst($billing) . ' ' . $planName
                                    : $planName;
                            } else {
                                $description = trim((string) ($row['s_name'] ?? 'Product Order'));
                            }
                        }

                        $customerName = trim((string) ($row['p_name'] ?? ''));
                        if ($customerName === '') {
                            $customerName = trim((string) ($row['user_name'] ?? ''));
                        }
                        if ($customerName === '') {
                            $customerName = trim((string) ($row['user_username'] ?? 'Unknown'));
                        }

                        $method = trim((string) ($row['p_method'] ?? '-'));
                        $statusRaw = strtolower(trim((string) ($row['p_status'] ?? 'pending')));
                        $statusClass = 'default';
                        if (in_array($statusRaw, ['paid', 'success', 'succeeded', 'completed', 'active'], true)) {
                            $statusClass = 'status-success';
                        } elseif (in_array($statusRaw, ['pending', 'refund_pending', 'processing'], true)) {
                            $statusClass = 'status-pending';
                        } elseif (in_array($statusRaw, ['cancelled', 'failed', 'refunded'], true)) {
                            $statusClass = 'status-cancelled';
                        }

                        $payDate = trim((string) ($row['p_date'] ?? ''));
                        $payTime = trim((string) ($row['p_time'] ?? ''));
                        $paidOn = trim($payDate . ' ' . $payTime);
                        if ($paidOn === '') {
                            $paidOn = '-';
                        }

                        $membershipName = trim((string) ($row['membership_plan_name'] ?? ''));
                        if ($paymentFor === 'membership' && $membershipName === '') {
                            $membershipName = $description;
                        }

                        $detailPayload = [
                            'payment_id' => (int) ($row['pay_id'] ?? 0),
                            'user_id' => (int) ($row['id'] ?? 0),
                            'customer_name' => $customerName,
                            'username' => (string) ($row['user_username'] ?? ''),
                            'email' => (string) ($row['user_email'] ?? ''),
                            'card_holder_name' => (string) ($row['p_name'] ?? ''),
                            'phone' => (string) ($row['p_phno'] ?? ''),
                            'address' => (string) ($row['p_address'] ?? ''),
                            'city' => (string) ($row['p_city'] ?? ''),
                            'state' => (string) ($row['p_state'] ?? ''),
                            'pincode' => (string) ($row['p_pincode'] ?? ''),
                            'payment_type' => $typeLabel,
                            'payment_for' => $paymentFor,
                            'description' => $description,
                            'amount' => number_format($amount, 2, '.', ''),
                            'method' => strtoupper($method),
                            'status' => ucfirst((string) ($row['p_status'] ?? 'pending')),
                            'date' => $payDate,
                            'time' => $payTime,
                            'stripe_intent' => (string) ($row['stripe_payment_intent_id'] ?? ''),
                            'stripe_status' => (string) ($row['stripe_payment_status'] ?? ''),
                            'sale_id' => isset($row['s_id']) && $row['s_id'] !== null ? (int) $row['s_id'] : null,
                            'membership_id' => isset($row['m_id']) && $row['m_id'] !== null ? (int) $row['m_id'] : null,
                            'product_name' => (string) ($row['s_name'] ?? ''),
                            'membership_name' => $membershipName,
                            'membership_billing' => ucfirst((string) ($row['billing_plan'] ?? '')),
                            'product_qty' => isset($row['s_quantity']) ? (int) $row['s_quantity'] : null,
                            'product_price' => isset($row['s_price']) && $row['s_price'] !== null ? number_format((float) $row['s_price'], 2, '.', '') : '',
                            'product_total' => isset($row['s_total']) && $row['s_total'] !== null ? number_format((float) $row['s_total'], 2, '.', '') : '',
                            'product_grand_total' => isset($row['s_grand_total']) && $row['s_grand_total'] !== null ? number_format((float) $row['s_grand_total'], 2, '.', '') : '',
                        ];
                        $detailJson = htmlspecialchars(
                            json_encode($detailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                ?>
                    <tr>
                        <td><?php echo $id_counter++; ?></td>
                        <td><?php echo (int) ($row['pay_id'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($customerName); ?></td>
                        <td><?php echo htmlspecialchars($typeLabel); ?></td>
                        <td>₹ <?php echo number_format($amount, 2); ?></td>
                        <td><?php echo htmlspecialchars(strtoupper($method)); ?></td>
                        <td>
                            <span class="payment-status-chip <?php echo htmlspecialchars($statusClass); ?>">
                                <?php echo htmlspecialchars($statusRaw); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($paidOn); ?></td>
                        <td>
                            <div class="action-dropdown">
                                <button type="button" class="action-dots" onclick="toggleActionDropdown(event, <?php echo (int) ($row['pay_id'] ?? 0); ?>)" aria-label="Open actions" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="action-dropdown-content">
                                    <button type="button" onclick='openPaymentViewModal(<?php echo $detailJson; ?>)'>
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="9" style="text-align:center;">No payment records found.</td></tr>';
                }
                ?>
                </tbody>
            </table>
            <?php 
            $params = $_GET;
            unset($params['page']);
            echo renderPagination($total_records, $current_page, $records_per_page, 'payment_manage.php', $params); 
            ?>
        </div>
    </div>
</div>

<div id="paymentViewModal" class="payment-view-modal">
    <div class="payment-view-card">
        <div class="payment-view-header">
            <h3 id="paymentViewTitle">Payment Details</h3>
            <button type="button" class="payment-view-close" onclick="closePaymentViewModal()">&times;</button>
        </div>
        <div class="payment-view-body" id="paymentViewBody">
            Loading...
        </div>
    </div>
</div>

<script>
    function escapePaymentHtml(value) {
        if (value === null || value === undefined) {
            return '-';
        }
        const text = String(value);
        if (text.trim() === '') {
            return '-';
        }
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function rowHtml(label, value) {
        return `<div class="payment-info-row"><span>${escapePaymentHtml(label)}</span><strong>${escapePaymentHtml(value)}</strong></div>`;
    }

    function openPaymentViewModal(data) {
        const modal = document.getElementById('paymentViewModal');
        const title = document.getElementById('paymentViewTitle');
        const body = document.getElementById('paymentViewBody');

        title.textContent = `Payment #${data.payment_id || '-'}`;

        let linkedRows = '';
        if (data.payment_for === 'product') {
            linkedRows += rowHtml('Sale ID', data.sale_id ?? '-');
            linkedRows += rowHtml('Product Name', data.product_name || '-');
            linkedRows += rowHtml('Quantity', data.product_qty ?? '-');
            linkedRows += rowHtml('Unit Price', data.product_price ? `₹ ${data.product_price}` : '-');
            linkedRows += rowHtml('Subtotal', data.product_total ? `₹ ${data.product_total}` : '-');
            linkedRows += rowHtml('Grand Total', data.product_grand_total ? `₹ ${data.product_grand_total}` : '-');
        } else {
            linkedRows += rowHtml('Membership ID', data.membership_id ?? '-');
            linkedRows += rowHtml('Membership Plan', data.membership_name || '-');
            linkedRows += rowHtml('Billing', data.membership_billing || '-');
        }

        body.innerHTML = `
            <div class="payment-view-grid">
                <div class="payment-view-block">
                    <h4>Customer</h4>
                    ${rowHtml('Name', data.customer_name || '-')}
                    ${rowHtml('Username', data.username || '-')}
                    ${rowHtml('Email', data.email || '-')}
                    ${rowHtml('Phone', data.phone || '-')}
                    ${rowHtml('Card Holder Name', data.card_holder_name || '-')}
                    ${rowHtml('Address', data.address || '-')}
                    ${rowHtml('City / State', `${data.city || '-'} / ${data.state || '-'}`)}
                    ${rowHtml('Pincode', data.pincode || '-')}
                </div>
                <div class="payment-view-block">
                    <h4>Payment</h4>
                    ${rowHtml('Payment Type', data.payment_type || '-')}
                    ${rowHtml('Description', data.description || '-')}
                    ${rowHtml('Amount', data.amount ? `₹ ${data.amount}` : '-')}
                    ${rowHtml('Method', data.method || '-')}
                    ${rowHtml('Status', data.status || '-')}
                    ${rowHtml('Date', data.date || '-')}
                    ${rowHtml('Time', data.time || '-')}
                    ${rowHtml('Stripe Intent', data.stripe_intent || '-')}
                    ${rowHtml('Stripe Status', data.stripe_status || '-')}
                </div>
                <div class="payment-view-block" style="grid-column: 1 / -1;">
                    <h4>Linked Record</h4>
                    ${linkedRows}
                </div>
            </div>
        `;

        modal.classList.add('show');
        if (typeof closeAllActionDropdowns === 'function') {
            closeAllActionDropdowns();
        }
    }

    function closePaymentViewModal() {
        document.getElementById('paymentViewModal').classList.remove('show');
    }

    document.addEventListener('click', function(event) {
        const modal = document.getElementById('paymentViewModal');
        if (event.target === modal) {
            closePaymentViewModal();
        }
    });
</script>
</body>
</html>
