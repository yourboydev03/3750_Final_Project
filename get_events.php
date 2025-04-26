<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

require 'db.php';
if ($mysqli->connect_errno) die('DB error');

$result = $mysqli->query("SELECT title, event_date, event_time, people FROM events ORDER BY event_date, event_time");

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode($events);
