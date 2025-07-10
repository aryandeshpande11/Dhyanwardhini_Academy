<?php
// Always show errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "dhyanawardhini";

// Connect to the database
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Detect which form is being submitted
$action = $_POST['action'] ?? '';

if ($action === "register") {
    // Registration
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password_raw = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password_raw !== $confirm) {
        echo "❌ Passwords do not match!";
        exit;
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $password);

    if ($stmt->execute()) {
        echo "✅ Registration successful!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

elseif ($action === "login") {
    // Login
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        echo "✅ Login successful!";
    } else {
        echo "❌ Invalid credentials!";
    }

    $stmt->close();
}

if ($action === "contact") {
    $email = $_POST['email'];
    $queryType = $_POST['query'];
    $userType = implode(", ", $_POST['user_type'] ?? []);
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO contacts (email, query_type, user_type, message) VALUES (?, ?, ?, ?)");

    if ($stmt === false) {
        die("SQL error: " . $conn->error); // 👈 Will now show exact problem
    }

    $stmt->bind_param("ssss", $email, $queryType, $userType, $message);

    if ($stmt->execute()) {
        echo "📨 Contact form submitted!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}