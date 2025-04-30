<?php
session_name('CALENDARSESSID');
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}

$theme = $_POST['theme'] ?? '';
$allowed = ['light','dark','ocean','forest','cherry','midnight'];
if (!in_array($theme, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Invalid theme']);
    exit;
}

$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB error']);
    exit;
}

$stmt = $mysqli->prepare(
    'UPDATE user_profiles SET theme = ? WHERE user_id = ?'
);
$stmt->bind_param('si', $theme, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();
$mysqli->close();

echo json_encode(['success'=>true]);








