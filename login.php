<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_name('CALENDARSESSID');
session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

// Process input
$email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Simple check
if ($email === '' || $password === '') {
    header('Location: login.html');
    exit;
}

// Connect to MySQL
$mysqli = new mysqli('localhost', 'devpatel_admin', 'Patel9317$$', 'devpatel_calendar');
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Look up user
if ($stmt = $mysqli->prepare('SELECT id, password FROM users WHERE email = ?')) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $hash);
        $stmt->fetch();
        
        // Verify password
        if (password_verify($password, $hash)) {
            // Success: set session and JS‐readable cookie
            $_SESSION['user_id'] = $userId;
            setcookie('calendar_loggedIn', 'true', time() + 3600, '/Assignments/Final_Project/', '', true, false);
            header('Location: showcalendar_inJS.html');
            exit;
        }
    }
    $stmt->close();
}

$mysqli->close();

// Login failed, redirect back to login
header('Location: login.html?error=1');
exit;

?>








