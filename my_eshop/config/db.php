<?php
// config/db.php

$servername = "localhost"; // or your server IP
$username = "root";        // your database username
$password = "";            // your database password
$dbname = "ecommerce_db";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set (good practice)
$conn->set_charset("utf8mb4");

// Start session if not already started - useful for cart, login status
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>