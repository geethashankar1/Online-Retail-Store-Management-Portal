<?php
// login.php
require_once 'config/db.php';
$message = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect if already logged in
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = $conn->real_escape_string(trim($_POST['email_or_username']));
    $password = $_POST['password'];

    if (empty($email_or_username) || empty($password)) {
        $message = "<div class='alert alert-danger'>Email/Username and Password are required.</div>";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ? OR username = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email_or_username, $email_or_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    // Example: Check if admin (you might have a 'role' column in users table)
                    // For simplicity, let's assume user with ID 1 is admin, or specific username
                    if ($user['username'] === 'admin' || $user['id'] === 1) { // Adjust admin check as needed
                        $_SESSION['is_admin'] = true;
                    }

                    header("Location: index.php"); // Redirect to home page or dashboard
                    exit;
                } else {
                    $message = "<div class='alert alert-danger'>Invalid email/username or password.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Invalid email/username or password.</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>My E-Shop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Login to Your Account</h2>
        <?php echo $message; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email_or_username">Email or Username:</label>
                <input type="text" id="email_or_username" name="email_or_username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>