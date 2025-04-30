<?php
session_name('CALENDARSESSID');
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$uid = $_SESSION['user_id'];
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$stmt = $mysqli->prepare(
    'SELECT id, title, event_date, event_time, description, location
       FROM events
      WHERE created_by = ?
      ORDER BY event_date, event_time'
);
if (! $stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('i', $uid);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode(['success' => true, 'events' => $events]);








