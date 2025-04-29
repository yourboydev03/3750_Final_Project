<?php
session_name('CALENDARSESSID');
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$id  = intval($_POST['id'] ?? 0);
$uid = $_SESSION['user_id'];

if (! $id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing event ID']);
    exit;
}

$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

$stmt = $mysqli->prepare(
    'DELETE FROM events
     WHERE id = ? AND created_by = ?'
);
if (! $stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('ii', $id, $uid);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Event not found or not yours'
    ]);
}

$stmt->close();
$mysqli->close();








