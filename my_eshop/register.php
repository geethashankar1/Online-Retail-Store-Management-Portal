<?php
// register.php
require_once 'config/db.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert alert-danger'>Password must be at least 6 characters long.</div>";
    } else {
        // Check if username or email already exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Username or Email already taken.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Securely hash the password

            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt_insert->execute()) {
                    $message = "<div class='alert alert-success'>Registration successful! You can now <a href='login.php'>login</a>.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error: " . $stmt_insert->error . "</div>";
                }
                $stmt_insert->close();
            } else {
                 $message = "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
            }
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - My E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>My E-Shop</h1>
         <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Create an Account</h2>
        <?php echo $message; ?>
        <form action="register.php" method="post" id="registerForm">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password (min 6 chars):</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My E-Shop</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>