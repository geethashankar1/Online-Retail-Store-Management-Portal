<?php
// checkout.php
require_once 'config/db.php';

// Ensure user is logged in to checkout
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_redirect_message'] = "Please login to proceed to checkout.";
    header("Location: login.php");
    exit;
}

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$message = '';
$cart_items = $_SESSION['cart'];
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_address = $conn->real_escape_string(trim($_POST['shipping_address']));
    // In a real system, you'd have payment processing here.
    // For this example, we'll just create an order.

    if (empty($shipping_address)) {
        $message = "<div class='alert alert-danger'>Shipping address is required.</div>";
    } else {
        $conn->begin_transaction(); // Start transaction

        try {
            // 1. Insert into orders table
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, 'Pending')");
            if (!$stmt_order) throw new Exception("Prepare failed (orders): " . $conn->error);
            
            $stmt_order->bind_param("ids", $_SESSION['user_id'], $total_amount, $shipping_address);
            if (!$stmt_order->execute()) throw new Exception("Execute failed (orders): " . $stmt_order->error);
            
            $order_id = $stmt_order->insert_id; // Get the ID of the newly inserted order
            $stmt_order->close();

            // 2. Insert into order_items table for each product in the cart
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            if (!$stmt_items) throw new Exception("Prepare failed (order_items): " . $conn->error);

            foreach ($cart_items as $item) {
                $stmt_items->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                if (!$stmt_items->execute()) throw new Exception("Execute failed (order_items): " . $stmt_items->error);
            }
            $stmt_items->close();

            $conn->commit(); // Commit transaction

            // 3. Clear the cart
            $_SESSION['cart'] = array();

            // 4. Redirect to a success page or order confirmation
            $message = "<div class='alert alert-success'>Order placed successfully! Your Order ID is: " . $order_id . "</div>";
            // header("Location: order_confirmation.php?order_id=" . $order_id);
            // For now, just show message
            
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $message = "<div class='alert alert-danger'>Order placement failed: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - My E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>My E-Shop</h1>
        <nav>
             <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Checkout</h2>
        <?php echo $message; ?>

        <?php if (!empty($cart_items) && strpos($message, 'Order placed successfully!') === false): // Show form if order not yet placed ?>
            <h3>Order Summary</h3>
            <?php foreach ($cart_items as $item): ?>
                <p><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>) - $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
            <?php endforeach; ?>
            <p><strong>Total: $<?php echo number_format($total_amount, 2); ?></strong></p>

            <form action="checkout.php" method="post">
                <div class="form-group">
                    <label for="shipping_address">Shipping Address:</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <h4>Payment Information</h4>
                    <p><em>Actual payment gateway integration is required for a real store. This is a placeholder.</em></p>
                    </div>

                <button type="submit" class="btn">Place Order</button>
            </form>
        <?php elseif (strpos($message, 'Order placed successfully!') === false): ?>
             <p>Your cart is empty or an error occurred. <a href="cart.php">Return to cart</a>.</p>
        <?php endif; ?>
         <p><a href="index.php">Continue Shopping</a></p>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>