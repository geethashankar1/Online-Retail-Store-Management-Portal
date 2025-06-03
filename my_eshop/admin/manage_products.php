<?php
// admin/manage_products.php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_redirect_message'] = "You are not authorized to access this page.";
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id_to_delete = intval($_GET['id']);
    
    // First, get the image name to delete the file
    $stmt_img = $conn->prepare("SELECT image FROM products WHERE id = ?");
    if ($stmt_img) {
        $stmt_img->bind_param("i", $product_id_to_delete);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();
        if ($row_img = $result_img->fetch_assoc()) {
            if (!empty($row_img['image']) && file_exists("../uploads/" . $row_img['image'])) {
                unlink("../uploads/" . $row_img['image']); // Delete the image file
            }
        }
        $stmt_img->close();
    }

    // Then delete the product from DB
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $product_id_to_delete);
        if ($stmt_delete->execute()) {
            $message = "<div class='alert alert-success'>Product deleted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting product: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error (prepare delete): " . $conn->error . "</div>";
    }
}


// Fetch products
$products_result = $conn->query("SELECT id, name, price, image FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Products</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <nav>
            <ul>
                <li><a href="../index.php">View Shop</a></li>
                <li><a href="add_product.php">Add Product</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="view_orders.php">View Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container admin-panel">
        <h2>Manage Products</h2>
        <?php echo $message; ?>
        <?php if (isset($_SESSION['product_update_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['product_update_message']; unset($_SESSION['product_update_message']); ?></div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($products_result && $products_result->num_rows > 0) {
                    while($product = $products_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>";
                        if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])) {
                            echo "<img src='../uploads/" . htmlspecialchars($product['image']) . "' alt='" . htmlspecialchars($product['name']) . "' style='width: 50px; height: auto;'>";
                        } else {
                            echo "No Image";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                        echo "<td>₹" . htmlspecialchars($product['price']) . "</td>";
                        echo "<td>";
                        // echo "<a href='edit_product.php?id=" . $product['id'] . "' class='btn btn-sm'>Edit</a> "; // You'd need an edit_product.php
                        echo "<a href='manage_products.php?action=delete&id=" . $product['id'] . "' class='btn btn-sm delete-product-btn' style='background-color: #dc3545;'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop Admin</p>
    </footer>
    <script src="../js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>