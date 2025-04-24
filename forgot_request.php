<?php
header('Content-Type: application/json');
ini_set('display_errors',1);
error_reporting(E_ALL);

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'error'=>'Please enter a valid email address']);
    exit;
}

// connect
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    echo json_encode(['success'=>false,'error'=>'Internal error']);
    exit;
}

// Find user_id
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_id);
$user_exists = $stmt->num_rows === 1;
if ($user_exists) {
    $stmt->fetch();
}
$stmt->close();

// Max 3 requests per hour per user
if ($user_exists) {
    $rl = $mysqli->prepare(
        "SELECT COUNT(*) FROM password_resets
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $rl->bind_param('i', $user_id);
    $rl->execute();
    $rl->bind_result($count);
    $rl->fetch();
    $rl->close();
    if ($count >= 3) {
        // Message to avoid enumeration
        echo json_encode([
          'success'=>false,
          'error'=>'Too many reset attempts. Please try again later.'
        ]);
        exit;
    }
}

// Clean up old/used tokens
$mysqli->query("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");

// If user exists, generate & store new code
if ($user_exists) {
    // 6-char hex code
    $token = strtoupper(bin2hex(random_bytes(3))); // e.g. "A1B2C3"
    $token_hash = password_hash($token, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', time() + 300); // 5 min
    
    $ins = $mysqli->prepare(
      'INSERT INTO password_resets (user_id, token_hash, expires_at)
       VALUES (?, ?, ?)'
    );
    $ins->bind_param('iss', $user_id, $token_hash, $expires);
    $ins->execute();
    $ins->close();
    
    // Send email
    $subject = 'Your Calendar App Reset Code';
    $message = "Your password reset code is: $token\nThis code expires in 5 minutes.";
    $headers = "From: no-reply@devpatel3750.com\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $subject, $message, $headers);
}

// Always respond success (avoids user-enumeration)
echo json_encode(['success'=>true]);








