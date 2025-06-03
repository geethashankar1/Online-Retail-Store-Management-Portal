<?php
// index.php
require_once 'config/db.php';

$search_term = '';
if (isset($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Home</title>
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
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                         <li><a href="admin/add_product.php">Admin Panel</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Our Products</h2>

        <form method="GET" action="index.php" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" class="btn">Search</button>
        </form>

        <div class="product-grid">
            <?php
            $sql = "SELECT id, name, price, image_url, description FROM products";
            if (!empty($search_term)) {
                $sql .= " WHERE name LIKE '%$search_term%' OR description LIKE '%$search_term%'";
            }
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='product-item'>";
                    if (!empty($row['image_url']) && file_exists('uploads/' . $row['image_url'])) {
                        echo "<img src='uploads/" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                    } else {
                         echo "<img src='uploads/default.jpg' alt='No Image Available'>";

                        
                    }
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p class='price'>₹" . htmlspecialchars($row['price']) . "</p>";
                    echo "<p>" . substr(htmlspecialchars($row['description']), 0, 100) . "...</p>"; // Short description
                    echo "<a href='product.php?id=" . $row['id'] . "' class='btn'>View Details</a> ";
                    echo "<a href='cart.php?action=add&id=" . $row['id'] . "' class='btn'>Add to Cart</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>