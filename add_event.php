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
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$uid    = $_SESSION['user_id'];
$title  = trim($_POST['title'] ?? '');
$date   = trim($_POST['event_date'] ?? '');
$time   = trim($_POST['event_time'] ?? '');
$people = trim($_POST['people'] ?? '');
$location = trim($_POST['location'] ?? '');

if (! $title || ! $date || ! $time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Title, date, and time are required']);
    exit;
}

$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$stmt = $mysqli->prepare(
    'INSERT INTO events (title, event_date, event_time, people, location, created_by)
     VALUES (?, ?, ?, ?, ?, ?)'
);
if (! $stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('sssssi', $title, $date, $time, $people, $location, $uid);
if (! $stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Insert failed']);
    $stmt->close();
    exit;
}

$newId = $stmt->insert_id;
$stmt->close();
$mysqli->close();

echo json_encode([
    'success' => true,
    'id'      => $newId
]);







