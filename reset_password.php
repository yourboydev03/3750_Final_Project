<?php
header('Content-Type: application/json');
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$code     = trim($_POST['code']  ?? '');
$password = $_POST['password']            ?? '';
$confirm  = $_POST['confirm_password']    ?? '';

if (!$email || !$code || !$password || !$confirm) {
    echo json_encode(['success'=>false,'error'=>'All fields are required']);
    exit;
}
if ($password !== $confirm) {
    echo json_encode(['success'=>false,'error'=>'Passwords do not match']);
    exit;
}

// Enforce strong password: min 8 chars, at least one letter, one number, one symbol
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password)) {
    echo json_encode([
      'success'=>false,
      'error'=>'Password must be at least 8 characters and include letters, numbers, and symbols.'
    ]);
    exit;
}

// Connection
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    echo json_encode(['success'=>false,'error'=>'Internal error']);
    exit;
}

// Clean up old/used tokens
$mysqli->query("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");

// Re-verify code as in verify_code.php
$sql = <<<SQL
SELECT pr.id, u.id AS uid
FROM password_resets pr
JOIN users u ON pr.user_id = u.id
WHERE u.email = ?
  AND pr.used = 0
  AND pr.expires_at >= NOW()
ORDER BY pr.created_at DESC
LIMIT 1
SQL;

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode(['success'=>false,'error'=>'Invalid or expired code']);
    exit;
}

$stmt->bind_result($reset_id, $user_id);
$stmt->fetch();
$stmt->close();

// Update password
$new_hash = password_hash($password, PASSWORD_DEFAULT);
$u1 = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
$u1->bind_param('si', $new_hash, $user_id);
$u1->execute();
$u1->close();

// Mark token as used
$u2 = $mysqli->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
$u2->bind_param('i', $reset_id);
$u2->execute();
$u2->close();

// Audit log
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
error_log("Password reset for user_id {$user_id} from IP {$ip} at " . date('c'));

echo json_encode(['success'=>true]);








