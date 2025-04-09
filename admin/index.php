<?php 
include('connect.php'); 
include('header.php'); 

// Assuming these are the maximum expected values (you can adjust accordingly)
$maxAppointments = 100; // Can be dynamically fetched
$maxOrders = 200; // Can be dynamically fetched
$maxSales = 10000; // Can be dynamically fetched
$maxMemberships = 300; // Can be dynamically fetched

// Query to get the total number of appointments in the last 24 hours
$appointmentsQuery = "SELECT COUNT(*) AS total_appointments FROM appointments WHERE a_date >= NOW() - INTERVAL 1 DAY";
$appointmentsResult = mysqli_query($con, $appointmentsQuery);
$totalAppointments = mysqli_fetch_assoc($appointmentsResult)['total_appointments'];

// Query to get the total number of orders in the last 24 hours
$ordersQuery = "SELECT COUNT(*) AS total_orders FROM product_sales WHERE s_date >= NOW() - INTERVAL 1 DAY";
$ordersResult = mysqli_query($con, $ordersQuery);
$totalOrders = mysqli_fetch_assoc($ordersResult)['total_orders'];

// Query to get the total sales in the last 24 hours
$salesQuery = "SELECT SUM(s_grand_total) AS total_sales FROM product_sales WHERE s_date >= NOW() - INTERVAL 1 DAY";
$salesResult = mysqli_query($con, $salesQuery);
$totalSales = mysqli_fetch_assoc($salesResult)['total_sales'];

// Query to get the total number of subscribed memberships in the last 24 hours
$membershipsQuery = "SELECT COUNT(*) AS total_memberships FROM membership_payments WHERE payment_date >= NOW() - INTERVAL 1 DAY"; // Ensure your membership table has the date column
$membershipsResult = mysqli_query($con, $membershipsQuery);
$totalMemberships = mysqli_fetch_assoc($membershipsResult)['total_memberships'];

// Calculate percentages dynamically based on the maximum values
$appointmentsPercent = $maxAppointments > 0 ? ($totalAppointments / $maxAppointments) * 100 : 0;
$ordersPercent = $maxOrders > 0 ? ($totalOrders / $maxOrders) * 100 : 0;
$salesPercent = $maxSales > 0 ? ($totalSales / $maxSales) * 100 : 0;
$membershipsPercent = $maxMemberships > 0 ? ($totalMemberships / $maxMemberships) * 100 : 0;

// Query to fetch recent payments
$query = "
    SELECT p.pay_id AS pay_id, p.p_name AS payment_name, p.p_method AS payment_method, ps.s_grand_total AS amount, p.p_status AS status, 'Product Payment' AS payment_type 
    FROM payment p
    JOIN product_sales ps ON p.s_id = ps.s_id
    WHERE ps.s_date >= NOW() - INTERVAL 1 DAY
    UNION ALL
    SELECT mp.m_id AS pay_id, mp.card_name AS payment_name, 'Credit Card' AS payment_method, mp.price AS amount, mp.status, 'Membership Payment' AS payment_type 
    FROM membership_payments mp
    WHERE mp.payment_date >= NOW() - INTERVAL 1 DAY
    ORDER BY pay_id DESC
    LIMIT 10
";
$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

$recentPayments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recentPayments[] = $row;
}

// Query to get the number of products sold each month for the current year
$salesDataQuery = "
    SELECT MONTH(s_date) AS month, COUNT(*) AS s_grand_total
    FROM product_sales
    WHERE YEAR(s_date) = YEAR(CURRENT_DATE)
    GROUP BY month
    ORDER BY month
";
$salesDataResult = mysqli_query($con, $salesDataQuery);

$salesData = array_fill(0, 12, 0); // Initialize an array for 12 months
while ($row = mysqli_fetch_assoc($salesDataResult)) {
    $salesData[$row['month'] - 1] = $row['s_grand_total']; // Store counts in the corresponding month index
}

// Convert the sales data into a JSON format for use in JavaScript
$salesDataJson = json_encode($salesData);
?>

<div class="main-content">
    <div class="content">
        <h1>Welcome to the Salon Management Admin Panel</h1>
        <p>Select an option from the sidebar to get started.</p>
    </div>
</div>

<div class="dashboard-container">
    <div class="dashboard">
        <div class="sales-card">
            <div class="icon">
                <img src="../products/appointment.png" alt="appointments icon">
            </div>
            <div class="sales-info">
                <h3>Appointments</h3>
                <p><?php echo $totalAppointments; ?></p>
                <span>Last 24 Hours</span>
            </div>
            <div class="progress-circle">
                <div class="circle" style="background: conic-gradient(#00c3a7 <?php echo round($appointmentsPercent); ?>%, #ddd <?php echo round($appointmentsPercent); ?>% 100%);">
                    <span><?php echo round($appointmentsPercent); ?>%</span>
                </div>
            </div>
        </div>

        <div class="sales-card">
            <div class="icon">
                <img src="../products/order.png" alt="orders icon">
            </div>
            <div class="sales-info">
                <h3>Orders</h3>
                <p><?php echo $totalOrders; ?></p>
                <span>Last 24 Hours</span>
            </div>
            <div class="progress-circle">
                <div class="circle" style="background: conic-gradient(#00c3a7 <?php echo round($ordersPercent); ?>%, #ddd <?php echo round($ordersPercent); ?>% 100%);">
                    <span><?php echo round($ordersPercent); ?>%</span>
                </div>
            </div>
        </div>

        <div class="sales-card">
            <div class="icon">
                <img src="../products/product.png" alt="sales icon">
            </div>
            <div class="sales-info">
                <h3>Total Sales</h3>
                <p>₹ <?php echo number_format($totalSales, 2); ?></p>
                <span>Last 24 Hours</span>
            </div>
            <div class="progress-circle">
                <div class="circle" style="background: conic-gradient(#00c3a7 <?php echo round($salesPercent); ?>%, #ddd <?php echo round($salesPercent); ?>% 100%);">
                    <span><?php echo round($salesPercent); ?>%</span>
                </div>
            </div>
        </div>
        
        <div class="sales-card">
            <div class="icon">
                <img src="../products/membership.png" alt="memberships icon">
            </div>
            <div class="sales-info">
                <h3>Memberships</h3>
                <p><?php echo $totalMemberships; ?></p>
                <span>Last 24 Hours</span>
            </div>
            <div class="progress-circle">
                <div class="circle" style="background: conic-gradient(#00c3a7 <?php echo round($membershipsPercent); ?>%, #ddd <?php echo round($membershipsPercent); ?>% 100%);">
                    <span><?php echo round($membershipsPercent); ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-layout">
        <div class="graph-container">
            <h2>Products Selling Graph</h2>
            <canvas id="salesGraph"></canvas>
        </div>

        <div class="recent-payments">
            <h2>Recent Payments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Name</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $id_counter=1;
                    foreach ($recentPayments as $payment): ?>
                        <tr>
                            <td><?php echo $id_counter++; ?></td>
                            <td><?php echo $payment['payment_name']; ?></td>
                            <td><?php echo $payment['payment_method']; ?></td>
                            <td>₹ <?php echo number_format($payment['amount'], 2); ?></td>
                            <td class="<?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </td>
                            <td><?php echo $payment['payment_type']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesData = <?php echo $salesDataJson; ?>; // Pass the PHP array to JavaScript

    const ctx = document.getElementById('salesGraph').getContext('2d');
    const salesGraph = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
                label: 'Products Sold',
                data: salesData, // Use the dynamic data
                backgroundColor: 'rgba(0, 191, 166, 0.2)',
                borderColor: '#cbb90f',
                borderWidth: 2,
                fill: true, // Fill area under the line
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true, // Start the y-axis at 0
                    ticks: {
                        callback: function(value) {
                            return Number.isInteger(value) ? value : ''; // Only show integer values
                        }
                    },
                }
            }
        }
    });
</script>
</body>
</html>
