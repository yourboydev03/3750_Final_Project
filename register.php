<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

// Collect, trim inputs
$full_name        = trim($_POST['full_name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if ($full_name === '' || $email === '' || $password === '' || $confirm_password === '') {
    header('Location: register.html?error=1');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.html?error=4');
    exit;
}
if ($password !== $confirm_password) {
    header('Location: register.html?error=2');
    exit;
}

// Connect to MySQL
$mysqli = new mysqli('localhost', 'devpatel_admin', 'Patel9317$$', 'devpatel_calendar');
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Ensure email is unique
$checkStmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?')
    or die('Prepare failed: ' . $mysqli->error);
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $mysqli->close();
    header('Location: register.html?error=3');
    exit;
}
$checkStmt->close();

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insertUser = $mysqli->prepare('INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)')
    or die('Prepare failed: ' . $mysqli->error);
$insertUser->bind_param('sss', $email, $hash, $full_name);
$insertUser->execute();

// Check that the user insert succeeded
if ($insertUser->affected_rows !== 1) {
    $insertUser->close();
    $mysqli->close();
    header('Location: register.html?error=1');
    exit;
}

$userId = $insertUser->insert_id;
$insertUser->close();

// Insert default profile
$insertProfile = $mysqli->prepare(
    'INSERT INTO user_profiles (user_id, display_name) VALUES (?, ?)'
) or die('Prepare failed: ' . $mysqli->error);
$insertProfile->bind_param('is', $userId, $full_name);
$insertProfile->execute();

// Check that the profile insert succeeded
if ($insertProfile->affected_rows !== 1) {
    $insertProfile->close();
    $mysqli->close();
    header('Location: register.html?error=1');
    exit;
}
$insertProfile->close();

// Clean and redirect
$mysqli->close();
header('Location: register.html?success=1');
exit;

?>






