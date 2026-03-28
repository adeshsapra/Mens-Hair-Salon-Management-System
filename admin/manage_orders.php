<?php 
include('header.php'); 
include('sidebar.php');
include('connect.php');
require_once '../user/wallet_helpers.php';
require_once 'pagination_helper.php';
require_once 'page_header_helper.php';

// Handle actions
if (isset($_GET['action']) && isset($_GET['s_id'])) {
    $s_id = intval($_GET['s_id']);
    $action = $_GET['action'];
    $new_status = '';
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    if ($action === 'confirm') {
        $new_status = 'confirmed';
    } elseif ($action === 'discard') {
        $new_status = 'cancelled';
    } elseif ($action === 'processing') {
        $new_status = 'processing';
    } elseif ($action === 'shipped') {
        $new_status = 'shipped';
    } elseif ($action === 'delivered') {
        $new_status = 'delivered';
    } elseif ($action === 'refund') {
        $orderResult = mysqli_query(
            $con,
            "SELECT ps.s_id, ps.id AS user_id, ps.s_total, ps.s_status, pay.pay_id, pay.p_method, pay.p_status
             FROM product_sales ps
             LEFT JOIN payment pay ON ps.s_id = pay.s_id
             WHERE ps.s_id = {$s_id}
             LIMIT 1"
        );

        if (!$orderResult || mysqli_num_rows($orderResult) === 0) {
            echo "<script>alert('Order not found for refund.'); window.location.href='manage_orders.php';</script>";
            exit;
        }

        $orderRow = mysqli_fetch_assoc($orderResult);
        $paymentMethod = strtolower(trim($orderRow['p_method'] ?? ''));
        $paymentStatus = strtolower(trim($orderRow['p_status'] ?? ''));
        $orderStatus = strtolower(trim($orderRow['s_status'] ?? ''));
        $refundUserId = (int) $orderRow['user_id'];
        $refundAmount = (float) $orderRow['s_total'];

        if (!in_array($paymentMethod, ['stripe', 'wallet'], true) || $orderStatus !== 'cancelled') {
            echo "<script>alert('Refund action allowed only for cancelled Stripe/Wallet orders.'); window.location.href='manage_orders.php';</script>";
            exit;
        }

        if ($paymentStatus === 'refunded') {
            echo "<script>alert('Order already refunded.'); window.location.href='manage_orders.php';</script>";
            exit;
        }

        mysqli_begin_transaction($con);
        try {
            $existingRefund = mysqli_query(
                $con,
                "SELECT id FROM wallet_transactions WHERE user_id = {$refundUserId} AND order_id = {$s_id} AND source = 'refund' LIMIT 1"
            );
            if ($existingRefund && mysqli_num_rows($existingRefund) > 0) {
                throw new Exception('Refund already exists in wallet history.');
            }

            $walletCredited = creditWalletBalance($con, $refundUserId, $refundAmount, 'refund', $s_id);
            if (!$walletCredited) {
                throw new Exception('Failed to credit wallet.');
            }

            $updateOrder = mysqli_query($con, "UPDATE product_sales SET s_status = 'refunded' WHERE s_id = {$s_id}");
            if (!$updateOrder) {
                throw new Exception('Failed to update order status.');
            }

            if (!empty($orderRow['pay_id'])) {
                $updatePayment = mysqli_query($con, "UPDATE payment SET p_status = 'refunded' WHERE pay_id = " . (int) $orderRow['pay_id']);
            } else {
                $updatePayment = mysqli_query($con, "UPDATE payment SET p_status = 'refunded' WHERE s_id = {$s_id} AND LOWER(p_method) IN ('stripe', 'wallet')");
            }
            if (!$updatePayment) {
                throw new Exception('Failed to update payment status.');
            }

            $insertHistory = mysqli_query(
                $con,
                "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$s_id}, 'refunded', '{$currentDate}', '{$currentTime}')"
            );
            if (!$insertHistory) {
                throw new Exception('Failed to write refund status history.');
            }

            mysqli_commit($con);
            echo "<script>alert('Refund processed and wallet credited successfully.'); window.location.href='manage_orders.php';</script>";
            exit;
        } catch (Exception $e) {
            mysqli_rollback($con);
            echo "<script>alert('Refund failed: " . addslashes($e->getMessage()) . "'); window.location.href='manage_orders.php';</script>";
            exit;
        }
    }

    if ($new_status !== '') {
        $update = mysqli_query($con, "UPDATE product_sales SET s_status = '$new_status' WHERE s_id = $s_id");
        
        // Log the status update with timestamp
        mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ($s_id, '$new_status', '$currentDate', '$currentTime')");

        // If delivered, also update payment status to paid for COD
        if ($new_status === 'delivered') {
            mysqli_query($con, "UPDATE payment SET p_status = 'paid' WHERE s_id = $s_id");
        }
        
        if ($update) {
            header("Location: manage_orders.php?toast=success&msg=" . urlencode('Order ' . ucfirst($new_status) . ' successfully!'));
            exit();
        } else {
            header("Location: manage_orders.php?toast=error&msg=" . urlencode('Failed to update order status.'));
            exit();
        }
    }
}

// Pagination Logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Count total records
$count_query = "SELECT COUNT(*) as total FROM product_sales";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];

$orders = "SELECT ps.*, pay.p_name, pay.p_phno, pay.p_address, pay.p_city, pay.p_state, pay.p_pincode, pay.p_method, pay.p_status as payment_status, pay.stripe_payment_intent_id 
           FROM product_sales ps 
           LEFT JOIN payment pay ON ps.s_id = pay.s_id 
           ORDER BY ps.s_id DESC
           LIMIT $offset, $records_per_page";
$orders_data = mysqli_query($con, $orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #cbb90f;
            --secondary-color: #18150d;
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --text-muted: #6c757d;
        }

        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-weight: 500;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #fce8e6; color: #d93025; }

        /* Actions Dropdown */
        .actions-dropdown {
            position: relative;
            display: inline-block;
        }

        .dots-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-dark);
            padding: 5px 10px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 150px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 100;
            border-radius: 8px;
            border: 1px solid #eee;
            overflow: hidden;
        }

        .dropdown-menu a {
            color: var(--text-dark);
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: background 0.2s;
        }

        .dropdown-menu a:hover {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }

        .dropdown-menu i {
            margin-right: 8px;
            width: 16px;
        }

        /* Show dropdown on click */
        .actions-dropdown.active .dropdown-menu {
            display: block;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 60%;
            max-width: 850px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .modal-header {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close-modal:hover {
            color: #fff;
        }

        .modal-body {
            padding: 30px;
        }

        .order-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }

        .detail-section h3 {
            font-size: 16px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-label {
            width: 120px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .info-value {
            flex: 1;
            color: var(--text-dark);
        }

        .modal-product-img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .history-timeline {
            margin-top: 20px;
            padding-left: 10px;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
            border-left: 2px solid var(--primary-color);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }

        .timeline-status {
            font-weight: bold;
            text-transform: capitalize;
            font-size: 13px;
        }

        .timeline-time {
            font-size: 12px;
            color: var(--text-muted);
        }

        .price-summary {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .price-total {
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: bold;
            font-size: 18px;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <?php
    renderAdminPageIntro(
        'Products / Manage Orders',
        'Order Management',
        'Track every order lifecycle stage, inspect details, and perform operational status actions.'
    );
    ?>

    <div class="main-content">
        <div class="content">
            <h2>Order Records</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($orders_data) > 0) {
                            $counter = 1;
                            while($row = mysqli_fetch_assoc($orders_data)) {
                                $current_status = strtolower($row['s_status']);
                                $payment_method = strtolower($row['p_method'] ?? '');
                                $payment_status = strtolower($row['payment_status'] ?? '');
                                $status_class = 'status-' . $current_status;
                                
                                // Fetch history for this order for the modal
                                $history_query = mysqli_query($con, "SELECT * FROM order_status_updates WHERE s_id = {$row['s_id']} ORDER BY id ASC");
                                $history = [];
                                while ($h = mysqli_fetch_assoc($history_query)) {
                                    $history[] = $h;
                                }
                                $row['history'] = $history;
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['p_name'] ?: 'N/A'); ?></td>
                                    <td><img src="../upload_product_photos/<?php echo $row['s_img']; ?>" class="profile-img" alt="Product"></td>
                                    <td><?php echo htmlspecialchars($row['s_name']); ?></td>
                                    <td>₹ <?php echo number_format($row['s_price']); ?></td>
                                    <td><?php echo $row['s_quantity']; ?></td>
                                    <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['s_status']); ?></span></td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button type="button" class="action-dots" onclick="toggleActionDropdown(event, <?php echo $row['s_id']; ?>)" aria-label="Open actions" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="action-dropdown-content">
                                                <a href="javascript:void(0)" onclick='openOrderModal(<?php echo json_encode($row); ?>)'>
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                <?php if ($current_status === 'pending') { ?>
                                                    <a href="manage_orders.php?action=confirm&s_id=<?php echo $row['s_id']; ?>" onclick="return confirm('Confirm this order?')">
                                                        <i class="fas fa-check"></i> Confirm Order
                                                    </a>
                                                    <a href="manage_orders.php?action=discard&s_id=<?php echo $row['s_id']; ?>" onclick="return confirm('Discard this order?')" class="delete-action">
                                                        <i class="fas fa-times"></i> Discard
                                                    </a>
                                                <?php } elseif ($current_status === 'confirmed') { ?>
                                                    <a href="manage_orders.php?action=processing&s_id=<?php echo $row['s_id']; ?>">
                                                        <i class="fas fa-spinner fa-spin"></i> Process Order
                                                    </a>
                                                <?php } elseif ($current_status === 'processing') { ?>
                                                    <a href="manage_orders.php?action=shipped&s_id=<?php echo $row['s_id']; ?>">
                                                        <i class="fas fa-shipping-fast"></i> Ship Order
                                                    </a>
                                                <?php } elseif ($current_status === 'shipped') { ?>
                                                    <a href="manage_orders.php?action=delivered&s_id=<?php echo $row['s_id']; ?>" onclick="return confirm('Mark as delivered?')">
                                                        <i class="fas fa-home"></i> Deliver Order
                                                    </a>
                                                <?php } elseif ($current_status === 'cancelled' && in_array($payment_method, ['stripe', 'wallet'], true) && $payment_status !== 'refunded') { ?>
                                                    <a href="manage_orders.php?action=refund&s_id=<?php echo $row['s_id']; ?>" onclick="return confirm('Process refund to user wallet?')">
                                                        <i class="fas fa-wallet"></i> Refund
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' style='text-align:center'>No orders found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php 
            // Display Pagination Links
            echo renderPagination($total_records, $current_page, $records_per_page, 'manage_orders.php'); 
            ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalOrderTitle">Order Details</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be injected via JS -->
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(btn) {
            document.querySelectorAll('.actions-dropdown').forEach(d => {
                if (d !== btn.parentElement) d.classList.remove('active');
            });
            btn.parentElement.classList.toggle('active');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.dots-btn') && !event.target.matches('.fa-ellipsis-v')) {
                document.querySelectorAll('.actions-dropdown').forEach(d => {
                    d.classList.remove('active');
                });
            }
        }

        function openOrderModal(order) {
            const modalBody = document.getElementById('modalBody');
            document.getElementById('modalOrderTitle').innerText = 'Order #' + order.s_id + ' Progress';
            
            let paymentInfo = '';
            if (order.p_method === 'stripe' && order.stripe_payment_intent_id) {
                paymentInfo = `<div class="info-row"><div class="info-label">Transaction:</div><div class="info-value">${order.stripe_payment_intent_id.substring(0, 15)}...</div></div>`;
            }

            let historyHtml = '';
            if (order.history && order.history.length > 0) {
                historyHtml = '<div class="history-timeline">';
                order.history.forEach(item => {
                    historyHtml += `
                        <div class="timeline-item">
                            <div class="timeline-status">${item.status}</div>
                            <div class="timeline-time">${item.update_date} at ${item.update_time}</div>
                        </div>
                    `;
                });
                historyHtml += '</div>';
            } else {
                historyHtml = '<p style="color:#999;font-size:13px;margin-top:10px">No status updates recorded yet.</p>';
            }

            modalBody.innerHTML = `
                <div class="order-detail-grid">
                    <div class="detail-section">
                        <h3>Order Status History</h3>
                        ${historyHtml}
                        
                        <h3 style="margin-top:25px">Product Info</h3>
                        <div style="display:flex; gap:15px; margin-bottom:15px">
                            <img src="../upload_product_photos/${order.s_img}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #eee">
                            <div>
                                <div style="font-weight:bold;color:var(--secondary-color)">${order.s_name}</div>
                                <div style="font-size:13px;color:var(--text-muted)">Size: ${order.s_size}</div>
                                <div style="font-size:13px;color:var(--text-muted)">Qty: ${order.s_quantity}</div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h3>Customer & Shipping</h3>
                        <div class="info-row"><div class="info-label">Customer:</div><div class="info-value">${order.p_name || 'N/A'}</div></div>
                        <div class="info-row"><div class="info-label">Contact:</div><div class="info-value">${order.p_phno || 'N/A'}</div></div>
                        <div class="info-row"><div class="info-label">Address:</div><div class="info-value">${order.p_address || 'N/A'}</div></div>
                        <div class="info-row"><div class="info-label">Location:</div><div class="info-value">${order.p_city}, ${order.p_state}</div></div>
                        
                        <h3 style="margin-top:20px">Payment Info</h3>
                        <div class="info-row"><div class="info-label">Method:</div><div class="info-value">${(order.p_method || 'N/A').toUpperCase()}</div></div>
                        <div class="info-row"><div class="info-label">Status:</div><div class="info-value">${(order.payment_status || 'pending').toUpperCase()}</div></div>
                        ${paymentInfo}

                        <div class="price-summary">
                            <div class="price-row"><span>Total Price</span><span>₹ ${order.s_total}</span></div>
                            <div class="price-total"><span>Grand Total</span><span>₹ ${order.s_grand_total}</span></div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('orderModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (!event.target.matches('.dots-btn') && !event.target.matches('.fa-ellipsis-v')) {
                document.querySelectorAll('.actions-dropdown').forEach(d => {
                    d.classList.remove('active');
                });
            }
        }
    </script>
</body>
</html>
