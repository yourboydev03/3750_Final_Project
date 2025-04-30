<?php
session_name('CALENDARSESSID');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$id     = intval($_POST['id'] ?? 0);
$title  = trim($_POST['title'] ?? '');
$date   = trim($_POST['event_date'] ?? '');
$time   = trim($_POST['event_time'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');

if (! $id || ! $title || ! $date || ! $time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$uid = $_SESSION['user_id'];
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$stmt = $mysqli->prepare(
    'UPDATE events
        SET title = ?, event_date = ?, event_time = ?, description = ?, location = ?
      WHERE id = ? AND created_by = ?'
);
if (! $stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('sssssii', $title, $date, $time, $description, $location, $id, $uid);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'No such event or no changes made'
    ]);
}

$stmt->close();
$mysqli->close();








