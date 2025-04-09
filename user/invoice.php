<?php
include 'connect.php';

session_start();
$o_time = isset($_GET['time']) ? mysqli_real_escape_string($con, $_GET['time']) : '';
$pay_fetch = mysqli_query($con, "SELECT * FROM product_sales WHERE s_time = '$o_time'");

$pay_fetch_detail = mysqli_query($con, "SELECT * FROM payment WHERE id = '{$_SESSION['user_id']}}'");
$pay_detail = mysqli_fetch_assoc($pay_fetch_detail);

$email = mysqli_query($con,"SELECT email FROM user_reg WHERE id = '{$_SESSION['user_id']}'");
$f_email = mysqli_fetch_assoc($email);

$invoice_number = mt_rand(100000, 999999);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" href="invoice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
  

  </style>
</head>
<body>
    <div class="invoice-container" id="invoice">
        <header>
            <div class="company-info">
                <img src="../photos/invoice.png" alt="Company Logo" class="logo" width="120px">
                <ul>
                <li><i class="fas fa-envelope"></i> classycut007@gmail.com</li>
                <li><i class="fas fa-phone"></i> +91 7575852866,  +91 9724564357</li>
                <li><i class="fas fa-map-marker-alt"></i> Shop No.7, Jesar Road, Savarkundla</li>
                </ul>
            </div>
            <div class="invoice-header">
                <div>
                    <strong>Invoice No:</strong> #<?php echo $invoice_number; ?>
                </div>
                <div>
                    <strong>Invoice Date:</strong> <?php echo date('Y-m-d'); ?>
                </div>
                <div class="total">
                    <strong>Total:</strong> <span>₹ <?php 
                        $total = 0;
                        while ($row = mysqli_fetch_assoc($pay_fetch)) {
                            $total += $row['s_total'];
                        }
                        echo $total; 
                    ?></span>
                </div>
            </div>
        </header>

        <section class="invoice-to">
            <h2>Invoice To</h2>
            <div class="details">
                <p><strong>Name : </strong><?php echo $pay_detail['p_name'];?></p> 
                <p><strong>Phone : </strong><?php echo $pay_detail['p_phno'];?></p>
                <p><strong>Email : </strong> <?php echo $f_email['email'];?></p> 
                <p><strong>Address : </strong><?php echo $pay_detail['p_address']; echo" , ";echo $pay_detail['p_city'];echo" , ";echo $pay_detail['p_state'];echo" , ";echo $pay_detail['p_pincode'];?></p>
            </div>
        </section>
        <section class="invoice-items">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset the query for fetching again
                    $pay_fetch = mysqli_query($con, "
                    SELECT * FROM product_sales WHERE s_time = '$o_time'");

                    while ($invoice_row = mysqli_fetch_assoc($pay_fetch)){
                    ?>
                    <tr>
                        <td><?php echo $invoice_row['s_id'];?></td>
                        <td><?php echo $invoice_row['s_name'];?></td>
                        <td>₹ <?php echo $invoice_row['s_price'];?></td>
                        <td><?php echo $invoice_row['s_quantity'];?></td>
                        <td>₹ <?php echo $invoice_row['s_total'];?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </section>
        <footer>
            <div class="thank-you">
                <h3>Terms & Condition.</h3>
                <p>Delivery dates are not guaranteed and Seller has no liability for damages that may be incurred due to any delay in shipment of goods hereunder.All claims relating to quantity or shipping errors.</p>
        </footer>
    </div>
    
    <div class="thank-you">
            </div>
            <div class="actions">
                <button class="print-btn" onclick="window.print()">Print</button>
                <button class="download-btn" onclick="downloadPDF()">Download</button>
            </div>

    <script>
        function downloadPDF() {
            const { jsPDF } = window.jspdf;

            const pdf = new jsPDF('p', 'mm', 'a4');
            const invoice = document.querySelector("#invoice");

            html2canvas(invoice, { scale: 2 }).then((canvas) => {
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210; // A4 size width in mm
                const pageHeight = 297; // A4 size height in mm
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                // First page
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // More pages if content is too large
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save('invoice.pdf');
            });
        }
    </script>
</body>
</html>
