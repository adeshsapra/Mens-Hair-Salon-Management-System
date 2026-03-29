<?php 
include 'connect.php';
include 'header.php'; 

$user_id = $_SESSION['user_id'];

// Query for product payments
$product_pay_fetch = mysqli_query($con, "
    SELECT product_sales.*, payment.*
    FROM product_sales
    JOIN payment ON product_sales.s_id = payment.s_id
    WHERE payment.id = '{$user_id}'");

// Query for membership payments
$membership_pay_fetch = mysqli_query($con, "
    SELECT membership_payments.*
    FROM membership_payments
    WHERE membership_payments.id = '{$user_id}'");

?>
<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Payment History</h1>
    </div>

    <h2 style="font-size: 20px; color: var(--bg1); margin-bottom: 1rem; border-bottom: 2px solid rgba(203,185,15,0.2); padding-bottom: 8px;"><i class="fas fa-shopping-bag" style="color: var(--brand); margin-right: 8px;"></i> Product Payments</h2>
    <div class="table-container" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 3rem;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: var(--bg1); color: var(--bg2);">
                    <th style="padding: 16px; font-weight: 500;">ID</th>
                    <th style="padding: 16px; font-weight: 500;">Name</th>
                    <th style="padding: 16px; font-weight: 500;">Mobile No.</th>
                    <th style="padding: 16px; font-weight: 500;">Method</th>
                    <th style="padding: 16px; font-weight: 500;">Total Amount</th>
                    <th style="padding: 16px; font-weight: 500;">Date</th>
                    <th style="padding: 16px; font-weight: 500;">Time</th>
                    <th style="padding: 16px; font-weight: 500;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($product_pay_fetch) > 0) {
                    $id_counter=1;
                    while($pay_fetch_row = mysqli_fetch_assoc($product_pay_fetch)){
                        $status = $pay_fetch_row["p_status"];
                        if (strtolower($status) == 'pending' || strtolower($status) == 'refund_pending') {
                            $color = '#b06000'; $bg = '#fef7e0'; $icon = 'fa-hourglass-half';
                        } elseif (strtolower($status) == 'refunded') {
                            $color = '#d93025'; $bg = '#fce8e6'; $icon = 'fa-wallet';
                        } elseif (strtolower($status) == 'received' || strtolower($status) == 'success' || strtolower($status) == 'paid') {
                            $color = '#1e8e3e'; $bg = '#e6f4ea'; $icon = 'fa-check-circle';
                        } else {
                            $color = '#d93025'; $bg = '#fce8e6'; $icon = 'fa-times-circle';
                        }
                ?>
                    <tr style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                        <td style="padding: 16px; color: #555;"><?php echo $id_counter++; ?></td>
                        <td style="padding: 16px; font-weight: 500; color: var(--bg1);"><?php echo htmlspecialchars($pay_fetch_row["p_name"]); ?></td>
                        <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($pay_fetch_row["p_phno"]); ?></td>
                        <td style="padding: 16px; color: #555;"><i class="fas fa-money-check-alt" style="color: var(--brand); margin-right: 4px;"></i> <?php echo htmlspecialchars($pay_fetch_row["p_method"]); ?></td>
                        <td style="padding: 16px; font-weight: 600; color: var(--bg1);">₹ <?php echo htmlspecialchars($pay_fetch_row["s_grand_total"]); ?></td>
                        <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($pay_fetch_row["p_date"]); ?></td>
                        <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($pay_fetch_row["p_time"]); ?></td>
                        <td style="padding: 16px;">
                            <span style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;">
                                <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="8" style="padding: 30px; text-align: center; color: #777;">No product payments found</td></tr>';
                }
                ?>
            </tbody>
        </table>            
    </div>

    <h2 style="font-size: 20px; color: var(--bg1); margin-bottom: 1rem; border-bottom: 2px solid rgba(203,185,15,0.2); padding-bottom: 8px;"><i class="fas fa-crown" style="color: var(--brand); margin-right: 8px;"></i> Membership Payments</h2>
    <div class="table-container" style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 3rem;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: var(--bg1); color: var(--bg2);">
                    <th style="padding: 16px; font-weight: 500;">ID</th>
                    <th style="padding: 16px; font-weight: 500;">Membership Type</th>
                    <th style="padding: 16px; font-weight: 500;">Card Name</th>
                    <th style="padding: 16px; font-weight: 500;">Phone Number</th>
                    <th style="padding: 16px; font-weight: 500;">Price</th>
                    <th style="padding: 16px; font-weight: 500;">Payment Date</th>
                    <th style="padding: 16px; font-weight: 500;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($membership_pay_fetch) > 0) {
                    $membership_counter = 1;
                    while($membership_row = mysqli_fetch_assoc($membership_pay_fetch)){
                        $status = $membership_row["status"];
                        $statusNorm = strtolower(trim((string) $status));
                        if ($statusNorm === 'pending') {
                            $color = '#b06000'; $bg = '#fef7e0'; $icon = 'fa-hourglass-half';
                        } elseif ($statusNorm === 'active' || $statusNorm === 'success') {
                            $color = '#1e8e3e'; $bg = '#e6f4ea'; $icon = 'fa-check-circle';
                        } else {
                            $color = '#d93025'; $bg = '#fce8e6'; $icon = 'fa-times-circle';
                        }
                ?>
                    <tr style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                        <td style="padding: 16px; color: #555;"><?php echo $membership_counter++; ?></td>
                        <td style="padding: 16px; font-weight: 500; color: var(--bg1);"><i class="fas fa-gem" style="color: var(--brand); margin-right: 4px;"></i> <?php echo htmlspecialchars($membership_row["membership_type"]); ?></td>
                        <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($membership_row["card_name"]); ?></td>
                        <td style="padding: 16px; color: #555;"><?php echo htmlspecialchars($membership_row["phone_number"]); ?></td>
                        <td style="padding: 16px; font-weight: 600; color: var(--bg1);">₹ <?php echo htmlspecialchars($membership_row["price"]); ?></td>
                        <td style="padding: 16px; color: #555;"><i class="far fa-calendar-alt" style="color: #999; margin-right: 4px;"></i> <?php echo htmlspecialchars($membership_row["payment_date"]); ?></td>
                        <td style="padding: 16px;">
                            <span style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;">
                                <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="7" style="padding: 30px; text-align: center; color: #777;">No membership payments found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
