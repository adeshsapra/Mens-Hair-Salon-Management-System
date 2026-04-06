<?php
include 'connect.php';
include 'header.php';
require_once '../admin/filter_helper.php';

$user_id = (int) $_SESSION['user_id'];

// Filter Configuration
$filterConfig = [
    'search' => ['col' => 'p_name', 'type' => 'like'],
    'status' => ['col' => 'p_status', 'type' => 'equals'],
    'method' => ['col' => 'p_method', 'type' => 'equals'],
    'start_date' => ['col' => 'p_date', 'type' => 'date_start'],
    'end_date' => ['col' => 'p_date', 'type' => 'date_end']
];

$whereClause = buildSimpleWhere($con, $filterConfig, " AND ");

$payment_query = "
    SELECT
        p.*,
        ps.s_name,
        mp.display_name AS membership_plan_name,
        mp.billing_plan
    FROM payment p
    LEFT JOIN product_sales ps ON p.s_id = ps.s_id
    LEFT JOIN membership_plans mp ON p.m_id = mp.mp_id
    WHERE p.id = {$user_id} $whereClause
    ORDER BY p.pay_id DESC
";
$payment_fetch = mysqli_query($con, $payment_query);
?>

<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Payment History</h1>
    </div>

    <div class="user-filter-section" style="margin-bottom: 30px;">
        <?php
        $filters = [
            [
                'type' => 'text',
                'name' => 'search',
                'placeholder' => 'Search by name...',
                'value' => $_GET['search'] ?? '',
                'label' => 'Payer Name'
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
                'name' => 'method',
                'label' => 'Method',
                'options' => [
                    '' => 'All Methods',
                    'cod' => 'COD',
                    'stripe' => 'Stripe',
                    'wallet' => 'Wallet'
                ],
                'value' => $_GET['method'] ?? ''
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    '' => 'All Status',
                    'paid' => 'Paid',
                    'received' => 'Received',
                    'pending' => 'Pending',
                    'refunded' => 'Refunded'
                ],
                'value' => $_GET['status'] ?? ''
            ]
        ];
        renderFilters($filters);
        ?>
    </div>

    <h2 style="font-size: 20px; color: var(--bg1); margin-bottom: 1rem; border-bottom: 2px solid rgba(203,185,15,0.2); padding-bottom: 8px;">
        <i class="fas fa-receipt" style="color: var(--brand); margin-right: 8px;"></i> Unified Payments
    </h2>
    <div class="table-container" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 3rem;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: var(--bg1); color: var(--bg2);">
                    <th style="padding: 16px; font-weight: 500;">ID</th>
                    <th style="padding: 16px; font-weight: 500;">Payment Of</th>
                    <th style="padding: 16px; font-weight: 500;">Description</th>
                    <th style="padding: 16px; font-weight: 500;">Name</th>
                    <th style="padding: 16px; font-weight: 500;">Phone</th>
                    <th style="padding: 16px; font-weight: 500;">Method</th>
                    <th style="padding: 16px; font-weight: 500;">Amount</th>
                    <th style="padding: 16px; font-weight: 500;">Reference</th>
                    <th style="padding: 16px; font-weight: 500;">Date</th>
                    <th style="padding: 16px; font-weight: 500;">Time</th>
                    <th style="padding: 16px; font-weight: 500;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($payment_fetch && mysqli_num_rows($payment_fetch) > 0) {
                    $counter = 1;
                    while ($row = mysqli_fetch_assoc($payment_fetch)) {
                        $paymentFor = strtolower((string) ($row['payment_for'] ?? ''));
                        if ($paymentFor === '') {
                            $paymentFor = !empty($row['m_id']) ? 'membership' : 'product';
                        }

                        $typeLabel = $paymentFor === 'membership' ? 'Membership Payment' : 'Product Payment';

                        $description = trim((string) ($row['payment_note'] ?? ''));
                        if ($description === '') {
                            if ($paymentFor === 'membership') {
                                $plan = trim((string) ($row['membership_plan_name'] ?? 'Membership'));
                                $billing = trim((string) ($row['billing_plan'] ?? ''));
                                $description = ($billing !== '' ? ucfirst($billing) . ' ' : '') . $plan;
                            } else {
                                $description = trim((string) ($row['s_name'] ?? 'Product order'));
                            }
                        }

                        $amount = (float) ($row['p_amount'] ?? 0);
                        $status = (string) ($row['p_status'] ?? '');
                        $statusNorm = strtolower(trim($status));
                        if ($statusNorm === 'pending' || $statusNorm === 'refund_pending') {
                            $color = '#b06000';
                            $bg = '#fef7e0';
                            $icon = 'fa-hourglass-half';
                        } elseif ($statusNorm === 'refunded') {
                            $color = '#d93025';
                            $bg = '#fce8e6';
                            $icon = 'fa-wallet';
                        } elseif ($statusNorm === 'received' || $statusNorm === 'success' || $statusNorm === 'paid' || $statusNorm === 'active') {
                            $color = '#1e8e3e';
                            $bg = '#e6f4ea';
                            $icon = 'fa-check-circle';
                        } else {
                            $color = '#d93025';
                            $bg = '#fce8e6';
                            $icon = 'fa-times-circle';
                        }

                        $reference = '-';
                        if (!empty($row['m_id'])) {
                            $reference = 'Membership ID: ' . (int) $row['m_id'];
                        } elseif (!empty($row['s_id'])) {
                            $reference = 'Sale ID: ' . (int) $row['s_id'];
                        }
                        if (!empty($row['stripe_payment_intent_id'])) {
                            $reference .= ' | Stripe: ' . $row['stripe_payment_intent_id'];
                        }
                ?>
                        <tr style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                            <td style="padding: 16px; color: #555;"><?php echo $counter++; ?></td>
                            <td style="padding: 16px; font-weight: 600; color: var(--bg1);"><?php echo htmlspecialchars($typeLabel); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($description); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars((string) ($row['p_name'] ?? '-')); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars((string) ($row['p_phno'] ?? '-')); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars((string) ($row['p_method'] ?? '-')); ?></td>
                            <td style="padding: 16px; font-weight: 600; color: var(--bg1);">₹ <?php echo number_format($amount, 2); ?></td>
                            <td style="padding: 16px; color: #555; font-size: 12px;"><?php echo htmlspecialchars($reference); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars((string) ($row['p_date'] ?? '-')); ?></td>
                            <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars((string) ($row['p_time'] ?? '-')); ?></td>
                            <td style="padding: 16px;">
                                <span style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;">
                                    <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="11" style="padding: 30px; text-align: center; color: #777;">No payments found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</main>