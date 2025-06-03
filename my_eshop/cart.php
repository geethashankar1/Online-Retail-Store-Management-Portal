<?php
// cart.php
require_once 'config/db.php'; // Session is started in db.php

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add item to cart
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Check if product exists and get details (optional, but good for price consistency)
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product_details = $result->fetch_assoc()) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = array(
                    'id' => $product_id,
                    'name' => $product_details['name'],
                    'price' => $product_details['price'],
                    'image' => $product_details['image'],
                    'quantity' => 1
                );
            }
        }
        $stmt->close();
    }
    // Redirect to cart page to prevent re-adding on refresh
    header('Location: cart.php');
    exit;
}

// Update item quantity
if (isset($_POST['action']) && $_POST['action'] == 'update' && isset($_POST['product_id'])) {
    $product_id_update = intval($_POST['product_id']);
    $quantity_update = intval($_POST['quantity']);

    if ($quantity_update > 0 && isset($_SESSION['cart'][$product_id_update])) {
        $_SESSION['cart'][$product_id_update]['quantity'] = $quantity_update;
    } elseif ($quantity_update <= 0 && isset($_SESSION['cart'][$product_id_update])) {
        unset($_SESSION['cart'][$product_id_update]); // Remove if quantity is 0 or less
    }
    header('Location: cart.php');
    exit;
}


// Remove item from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $product_id_remove = intval($_GET['id']);
    if (isset($_SESSION['cart'][$product_id_remove])) {
        unset($_SESSION['cart'][$product_id_remove]);
    }
    header('Location: cart.php');
    exit;
}

// Clear cart
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['cart'] = array();
    header('Location: cart.php');
    exit;
}

$cart_items = $_SESSION['cart'];
$total_price = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>My E-Shop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart (<?php echo count($cart_items); ?>)</a></li>
                 <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Your Shopping Cart</h2>
        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <?php
                    if (!empty($item['image_url']) && file_exists('uploads/' . $item['image_url'])) {
                        echo "<img src='uploads/" . htmlspecialchars($item['image_url']) . "' alt='" . htmlspecialchars($item['name']) . "'>";
                    } else {
                         echo "<img src='uploads/default_placeholder.png' alt='Default image'>";
                    }
                    ?>
                    <div>
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                        Price: ₹<?php echo htmlspecialchars($item['price']); ?>
                    </div>
                    <div>
                        <form action="cart.php" method="post" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            Quantity: <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px;">
                            <button type="submit" class="btn btn-sm">Update</button>
                        </form>
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm" style="background-color: #dc3545;">Remove</a>
                    </div>
                    <div>
                        Subtotal: ₹<?php
                        $subtotal = $item['price'] * $item['quantity'];
                        echo number_format($subtotal, 2);
                        $total_price += $subtotal;
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cart-total">
                <strong>Total: ₹<?php echo number_format($total_price, 2); ?></strong>
            </div>
            <div style="margin-top: 20px;">
                <a href="cart.php?action=clear" class="btn" style="background-color: #ffc107; color: black;">Clear Cart</a>
                <a href="checkout.php" class="btn" style="float: right;">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <p>Your cart is empty. <a href="index.php">Continue shopping!</a></p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>