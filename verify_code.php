<?php
header('Content-Type: application/json');
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$code  = trim($_POST['code']  ?? '');
if (!$email || !$code) {
    echo json_encode(['success'=>false,'error'=>'Missing parameters']);
    exit;
}

// Connection
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    echo json_encode(['success'=>false,'error'=>'Internal error']);
    exit;
}

// Lookup most recent, unused, unexpired reset for email
$sql = <<<SQL
SELECT pr.id, pr.token_hash
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
    echo json_encode([
      'success'=>false,
      'error'=>'Invalid or expired code. Please request a new code.'
    ]);
    exit;
}

$stmt->bind_result($reset_id, $token_hash);
$stmt->fetch();
$stmt->close();

// Compare codes in constant time
if (!password_verify($code, $token_hash)) {
    echo json_encode([
      'success'=>false,
      'error'=>'Invalid or expired code. Please request a new code.'
    ]);
    exit;
}

echo json_encode(['success'=>true]);








