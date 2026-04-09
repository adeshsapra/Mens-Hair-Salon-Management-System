<?php 
include('connect.php'); 
include('header.php'); 
require_once('pagination_helper.php');
require_once('page_header_helper.php');

// Max values for percentage markers
$maxAppointments = 100;
$maxOrders = 200;
$maxSales = 20000;
$maxMemberships = 100;

// --- METRICS FETCHING ---
// Appointments
$allTimeAppointments = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM appointments"))['total'];
$recentAppointmentsCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM appointments WHERE a_date >= NOW() - INTERVAL 1 DAY"))['total'];

// Orders
$allTimeOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM product_sales"))['total'];
$recentOrdersCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM product_sales WHERE s_date >= NOW() - INTERVAL 1 DAY"))['total'];

// Sales
$allTimeSales = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(s_grand_total) AS total FROM product_sales"))['total'] ?? 0;
$recentSalesValue = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(s_grand_total) AS total FROM product_sales WHERE s_date >= NOW() - INTERVAL 1 DAY"))['total'] ?? 0;

// Memberships
$allTimeMemberships = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM payment WHERE (payment_for = 'membership' OR m_id IS NOT NULL)"))['total'];
$recentMembershipsCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM payment WHERE (payment_for = 'membership' OR m_id IS NOT NULL) AND TIMESTAMP(p_date, p_time) >= NOW() - INTERVAL 1 DAY"))['total'];

// Percentages
$appointmentsPercent = $maxAppointments > 0 ? ($recentAppointmentsCount / $maxAppointments) * 100 : 0;
$ordersPercent = $maxOrders > 0 ? ($recentOrdersCount / $maxOrders) * 100 : 0;
$salesPercent = $maxSales > 0 ? ($recentSalesValue / $maxSales) * 100 : 0;
$membershipsPercent = $maxMemberships > 0 ? ($recentMembershipsCount / $maxMemberships) * 100 : 0;

// Pagination for Recent Payments (The small table on the right)
$recent_payments_per_page = 10;
$recent_payments_page = isset($_GET['dashboard_page']) ? (int) $_GET['dashboard_page'] : 1;
if ($recent_payments_page < 1) $recent_payments_page = 1;
$recent_count_query = "SELECT COUNT(*) AS total FROM payment WHERE TIMESTAMP(p_date, p_time) >= NOW() - INTERVAL 1 DAY";
$recent_total_records = (int) mysqli_fetch_assoc(mysqli_query($con, $recent_count_query))['total'];
$recent_total_pages = max(1, (int) ceil($recent_total_records / $recent_payments_per_page));
if ($recent_payments_page > $recent_total_pages) $recent_payments_page = $recent_total_pages;
$recent_offset = ($recent_payments_page - 1) * $recent_payments_per_page;

$payments_query = "
    SELECT p.pay_id, p.p_name, p.p_method, COALESCE(p.p_amount, ps.s_grand_total, 0) AS amount, p.p_status AS status,
           CASE WHEN (p.payment_for = 'membership' OR p.m_id IS NOT NULL) THEN 'Membership' ELSE 'Product' END AS payment_type
    FROM payment p LEFT JOIN product_sales ps ON p.s_id = ps.s_id
    WHERE TIMESTAMP(p.p_date, p.p_time) >= NOW() - INTERVAL 1 DAY
    ORDER BY p.pay_id DESC LIMIT $recent_offset, $recent_payments_per_page";
$result = mysqli_query($con, $payments_query);
$recentPayments = [];
while ($row = mysqli_fetch_assoc($result)) { $recentPayments[] = $row; }

// --- COMBINED RECENT ACTIVITY (NEW SECTION) ---
// Fetch latest 5 appointments
$latestApps = mysqli_query($con, "SELECT ah_id as id, ah_name as name, ah_date as date, ah_status as status, 'Appointment' as type FROM appointment_history ORDER BY ah_id DESC LIMIT 5");
// Fetch latest 5 orders
$latestOrders = mysqli_query($con, "SELECT ps.s_id as id, pay.p_name as name, ps.s_date as date, ps.s_status as status, 'Order' as type FROM product_sales ps LEFT JOIN payment pay ON ps.s_id = pay.s_id ORDER BY ps.s_id DESC LIMIT 5");

$combinedActivity = [];
while($row = mysqli_fetch_assoc($latestApps)) { $combinedActivity[] = $row; }
while($row = mysqli_fetch_assoc($latestOrders)) { $combinedActivity[] = $row; }

// Sort combined activity by date (Descending)
usort($combinedActivity, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$combinedActivity = array_slice($combinedActivity, 0, 8); // Keep latest 8 items

// Chart Data
$salesDataQuery = "SELECT MONTH(s_date) AS month, COUNT(*) AS total FROM product_sales WHERE YEAR(s_date) = YEAR(CURRENT_DATE) GROUP BY month ORDER BY month";
$salesDataResult = mysqli_query($con, $salesDataQuery);
$salesData = array_fill(0, 12, 0);
while ($row = mysqli_fetch_assoc($salesDataResult)) { $salesData[$row['month'] - 1] = $row['total']; }
$salesDataJson = json_encode($salesData);
?>

<?php
renderAdminPageIntro(
    'Dashboard', 'Salon Analytics Overview',
    'Complete performance summary of your salon, tracking both all-time growth and daily activity.'
);
?>

<div class="dashboard-container">
    <div class="dashboard-metrics-grid">
        <div class="metric-card">
            <div class="metric-icon appointment"><i class="fas fa-calendar-check"></i></div>
            <div class="metric-data">
                <span class="metric-title">Appointments</span>
                <div class="metric-counts">
                    <h2 class="all-time"><?php echo number_format($allTimeAppointments); ?></h2>
                    <div class="recent-stats"><span class="recent-count"><?php echo $recentAppointmentsCount; ?></span><span class="recent-label">in last 24h</span></div>
                </div>
            </div>
            <div class="metric-trend"><div class="trend-circle" style="--percent: <?php echo min(100, round($appointmentsPercent)); ?>%"><svg><circle cx="25" cy="25" r="20"></circle><circle cx="25" cy="25" r="20"></circle></svg><span><?php echo round($appointmentsPercent); ?>%</span></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-icon orders"><i class="fas fa-shopping-bag"></i></div>
            <div class="metric-data">
                <span class="metric-title">Product Orders</span>
                <div class="metric-counts">
                    <h2 class="all-time"><?php echo number_format($allTimeOrders); ?></h2>
                    <div class="recent-stats"><span class="recent-count"><?php echo $recentOrdersCount; ?></span><span class="recent-label">in last 24h</span></div>
                </div>
            </div>
            <div class="metric-trend"><div class="trend-circle" style="--percent: <?php echo min(100, round($ordersPercent)); ?>%"><svg><circle cx="25" cy="25" r="20"></circle><circle cx="25" cy="25" r="20"></circle></svg><span><?php echo round($ordersPercent); ?>%</span></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-icon sales"><i class="fas fa-chart-line"></i></div>
            <div class="metric-data">
                <span class="metric-title">Total Revenue</span>
                <div class="metric-counts">
                    <h2 class="all-time">₹ <?php echo number_format($allTimeSales, 2); ?></h2>
                    <div class="recent-stats"><span class="recent-count">₹ <?php echo number_format($recentSalesValue, 0); ?></span><span class="recent-label">in last 24h</span></div>
                </div>
            </div>
            <div class="metric-trend"><div class="trend-circle" style="--percent: <?php echo min(100, round($salesPercent)); ?>%"><svg><circle cx="25" cy="25" r="20"></circle><circle cx="25" cy="25" r="20"></circle></svg><span><?php echo round($salesPercent); ?>%</span></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-icon member"><i class="fas fa-id-card"></i></div>
            <div class="metric-data">
                <span class="metric-title">Active Plans</span>
                <div class="metric-counts">
                    <h2 class="all-time"><?php echo number_format($allTimeMemberships); ?></h2>
                    <div class="recent-stats"><span class="recent-count"><?php echo $recentMembershipsCount; ?></span><span class="recent-label">in last 24h</span></div>
                </div>
            </div>
            <div class="metric-trend"><div class="trend-circle" style="--percent: <?php echo min(100, round($membershipsPercent)); ?>%"><svg><circle cx="25" cy="25" r="20"></circle><circle cx="25" cy="25" r="20"></circle></svg><span><?php echo round($membershipsPercent); ?>%</span></div></div>
        </div>
    </div>

    <div class="content-layout">
        <div class="graph-container" style="height: 480px; position: relative;">
            <div class="container-header"><h2>Product Sales Volume</h2><span class="subtitle">Unit sales distribution across the current year</span></div>
            <div style="flex-grow: 1; min-height: 0;"><canvas id="salesGraph"></canvas></div>
        </div>
        <div class="recent-payments">
            <div class="container-header"><h2>Recent Transactions</h2><span class="subtitle">Last 10 payments processed today</span></div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>ID</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Type</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?><tr><td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">No transactions found.</td></tr><?php else: ?>
                            <?php foreach ($recentPayments as $p): ?><tr><td>#<?php echo $p['pay_id']; ?></td><td><strong><?php echo htmlspecialchars($p['p_name']); ?></strong></td><td><?php echo htmlspecialchars($p['p_method']); ?></td><td>₹ <?php echo number_format($p['amount'], 2); ?></td><td><span class="payment-status-chip <?php echo strtolower($p['status']); ?>"><?php echo ucfirst($p['status']); ?></span></td><td><span class="type-tag"><?php echo $p['payment_type']; ?></span></td></tr><?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-pagination"><?php echo renderPagination($recent_total_records, $recent_payments_page, $recent_payments_per_page, 'index.php', [], 'dashboard_page'); ?></div>
        </div>
    </div>

    <!-- NEW SECTION: RECENT BUSINESS ACTIVITY -->
    <div class="activity-section">
        <div class="container-header">
            <h2>Recent Business Activity</h2>
            <span class="subtitle">Combined history of latest booking requests and product orders</span>
        </div>
        <div class="activity-feed-container">
            <?php if (empty($combinedActivity)): ?>
                <div class="activity-empty-state">No recent activity recorded.</div>
            <?php else: ?>
                <?php foreach($combinedActivity as $item):
                    $link = ($item['type'] === 'Appointment') ? 'appointments_manage.php' : 'manage_orders.php';
                    $typeClass = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $item['type']));
                    $statusClass = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $item['status']));
                    $statusLabel = ucwords(str_replace(['-', '_'], ' ', (string) $item['status']));
                    $customerName = trim((string) ($item['name'] ?? '')) !== '' ? $item['name'] : 'N/A';
                    $iconClass = ($item['type'] === 'Appointment') ? 'fa-calendar-check' : 'fa-shopping-bag';
                ?>
                    <a class="activity-feed-item" href="<?php echo htmlspecialchars($link); ?>">
                        <span class="activity-icon-box <?php echo htmlspecialchars($typeClass); ?>">
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </span>
                        <div class="activity-content-box">
                            <div class="activity-top-row">
                                <span class="activity-type"><?php echo htmlspecialchars($item['type']); ?></span>
                                <span class="activity-ref">#<?php echo htmlspecialchars((string) $item['id']); ?></span>
                                <strong class="activity-customer"><?php echo htmlspecialchars($customerName); ?></strong>
                            </div>
                            <div class="activity-bottom-row">
                                <span class="activity-date"><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($item['date'])); ?></span>
                                <span class="status-label <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                            </div>
                        </div>
                        <span class="activity-arrow-box">Manage <i class="fas fa-arrow-right"></i></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesData = <?php echo $salesDataJson; ?>;
    const ctx = document.getElementById('salesGraph').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(203, 185, 15, 0.3)'); gradient.addColorStop(1, 'rgba(203, 185, 15, 0)');
    new Chart(ctx, {
        type: 'line', data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{ label: 'Units Sold', data: salesData, backgroundColor: gradient, borderColor: '#cbb90f', borderWidth: 3, pointBackgroundColor: '#fff', pointBorderColor: '#cbb90f', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1, font: { family: 'Inter', size: 12 } } }, x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 12 } } } }
        }
    });
</script>
</body></html>
