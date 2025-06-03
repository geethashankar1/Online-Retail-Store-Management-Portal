<?php
// product.php
require_once 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = intval($_GET['id']); // Sanitize input

$sql = "SELECT id, name, description, price, image_url FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "<p>Product not found.</p>";
    // Optionally, include header/footer or redirect
    exit;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - My E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>My E-Shop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a></li>
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
        <div class="product-detail">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <?php
            if (!empty($product['image_url']) && file_exists('uploads/' . $product['image_url'])) {
                echo "<img src='uploads/" . htmlspecialchars($product['image_url']) . "' alt='" . htmlspecialchars($product['name']) . "' style='max-width: 400px; margin-bottom: 20px;'>";
            } else {
                echo "<img src='uploads/default_placeholder.png' alt='Default image' style='max-width: 400px; margin-bottom: 20px;'>";
            }
            ?>
            <p class="price">Price: $<?php echo htmlspecialchars($product['price']); ?></p>
            <h3>Description:</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn">Add to Cart</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>