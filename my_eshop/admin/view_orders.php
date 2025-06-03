<?php
// admin/view_orders.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_redirect_message'] = "You are not authorized to access this page.";
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id_update = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $stmt_update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("si", $new_status, $order_id_update);
        if ($stmt_update->execute()) {
            $message = "<div class='alert alert-success'>Order status updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update order status: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error (prepare update): " . $conn->error . "</div>";
    }
}

// Fetch orders with user details
$orders_sql = "
    SELECT o.id, o.total_amount, o.created_at, o.status, o.shipping_address, u.username AS customer_name, u.email AS customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$orders_result = $conn->query($orders_sql);

// Possible statuses for dropdown
$possible_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>
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
        <h2>View Orders</h2>
        <?php echo $message; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Shipping Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($orders_result && $orders_result->num_rows > 0) {
                    while($order = $orders_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $order['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($order['customer_name']) . "<br><small>(" . htmlspecialchars($order['customer_email']) . ")</small></td>";
                        echo "<td>₹" . number_format($order['total_amount'], 2) . "</td>";
                        echo "<td>" . date("M d, Y H:i", strtotime($order['created_at'])) . "</td>";
                        echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                        echo "<td>" . nl2br(htmlspecialchars($order['shipping_address'])) . "</td>";
                        echo "<td>
                                <form method='POST' action='view_orders.php' style='display:inline;'>
                                    <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                                    <select name='status' style='padding: 5px;'>";
                                foreach ($possible_statuses as $status_option) {
                                    echo "<option value='" . htmlspecialchars($status_option) . "'" . ($order['status'] == $status_option ? " selected" : "") . ">" . htmlspecialchars($status_option) . "</option>";
                                }
                        echo       "</select>
                                    <button type='submit' name='update_status' class='btn btn-sm'>Update</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No orders found.</td></tr>";
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