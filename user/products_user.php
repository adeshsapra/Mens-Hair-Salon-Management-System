<?php 
include 'connect.php';
include 'header.php';

$errors = [];

if(isset($_POST['update_update_btn'])){
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];

    $product_query = mysqli_query($con, "SELECT p_id FROM product_cart WHERE c_id = '$update_id'");
    $product_data = mysqli_fetch_assoc($product_query);
    $product_id = $product_data['p_id'];

    $stock_query = mysqli_query($con, "SELECT p_quantity FROM products WHERE p_id = '$product_id'");
    $stock_data = mysqli_fetch_assoc($stock_query);
    $available_stock = $stock_data['p_quantity'];

    if ($update_value > $available_stock) {
        $errors[$update_id] = "Not Available!";
    } else { 
        // Update quantity and sub_total
        $update_total_query = mysqli_query($con, "UPDATE `product_cart` SET c_quantity = '$update_value', c_total = c_price * '$update_value' WHERE c_id = '$update_id'");
        if($update_total_query){
            // Recalculate the grand total
            $grand_total_query = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '{$_SESSION['user_id']}'");
            $grand_total_data = mysqli_fetch_assoc($grand_total_query);
            $grand_total = $grand_total_data['grand_total'];

            // Update grand total in the database
            mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = '$grand_total' WHERE id = '{$_SESSION['user_id']}'");

            header('location:products_user.php');
            exit();
        }
    }
}

if(isset($_GET['id'])){
    $remove_id = $_GET['id'];

    // Remove product from cart
    mysqli_query($con, "DELETE FROM `product_cart` WHERE c_id = '$remove_id'");

    // Recalculate the grand total after removal
    $grand_total_query = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '{$_SESSION['user_id']}'");
    $grand_total_data = mysqli_fetch_assoc($grand_total_query);
    $grand_total = $grand_total_data['grand_total'] ?? 0;

    // Update the grand total after removal
    mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = '$grand_total' WHERE id = '{$_SESSION['user_id']}'");

    header('location:products_user.php');
    exit();
}

if(isset($_GET['delete_all'])){
    mysqli_query($con, "DELETE FROM `product_cart` WHERE id = '{$_SESSION['user_id']}'");

    // Set grand total to 0 after all products are removed
    mysqli_query($con, "UPDATE `product_cart` SET c_grand_total = 0 WHERE id = '{$_SESSION['user_id']}'");

    header('location:products_user.php');
    exit();
}

$empty_cart_message = '';
$select_cart = mysqli_query($con, "
    SELECT product_cart.*, products.*
    FROM product_cart
    JOIN products ON product_cart.p_id = products.p_id
    WHERE product_cart.id = '{$_SESSION['user_id']}'
");
if (mysqli_num_rows($select_cart) === 0) {
    $empty_cart_message = 'Your cart is empty.';
}
?>

<main class="content">
<div class="product-container">
<section class="shopping-cart">
   <h1 class="heading">shopping cart</h1>

   <?php if ($empty_cart_message): ?>
        <div class="empty-cart-message">
         <?php echo $empty_cart_message; ?>
         <br><a href="../eshop.php" class="empty_cart_button">Shop Now</a>
      </div>
    <?php else: ?>
        <table>
            <thead>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Action</th>
            </thead>
            <tbody>
            <?php 
                $grand_total = 0;
                if (mysqli_num_rows($select_cart) > 0) {
                    while ($fetch_product = mysqli_fetch_assoc($select_cart)) {
                        $price = (float)$fetch_product['p_price'];
                        $quantity = (int)$fetch_product['c_quantity'];
                        $sub_total = $price * $quantity;

                        $error_message = isset($errors[$fetch_product['c_id']]) ? $errors[$fetch_product['c_id']] : '';
            ?>
            <tr>
                <td><img src="../upload_product_photos/<?php echo $fetch_product['p_img']; ?>" height="150" alt=""></td>
                <td><?php echo $fetch_product['p_name']; ?></td> 
                <td><?php echo number_format($price, 2); ?></td>
                <td><?php echo $fetch_product['p_size']; ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="update_quantity_id" value="<?php echo $fetch_product['c_id']; ?>">
                        <input type="number" name="update_quantity" min="1" value="<?php echo $fetch_product['c_quantity']; ?>">
                        <input type="submit" value="Confirm" name="update_update_btn">
                        <?php if ($error_message): ?>
                            <div class="message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                    </form>   
                </td>
                <td><?php echo number_format($sub_total, 2); ?></td>
                <td><a href="products_user.php?id=<?php echo $fetch_product['c_id']; ?>" onclick="return confirm('Remove item from cart?')" class="delete-product-btn"> <i class="fas fa-trash"></i> Remove</a></td>
            </tr>
            <?php
                    $grand_total += $sub_total;  
                    }
                }
            ?>
            <tr class="table-bottom" id="table-bottom">
                <td><a href="../eshop.php" id="get-more" style="margin-top: 0;">Get More Products</a></td>
                <td colspan="4">Grand Total</td>
                <td><?php echo number_format($grand_total, 2); ?></td>
                <td><a href="products_user.php?delete_all" onclick="return confirm('Are you sure you want to delete all?');" class="delete_all"> <i class="fas fa-trash"></i> Delete All </a></td>
            </tr>
            </tbody>
        </table>

   <div class="checkout-btn">
      <a href="checkout.php" class="proceed-btn">proceed to checkout</a>
   </div>
   <?php endif; ?>

</section>
</div>
</main>