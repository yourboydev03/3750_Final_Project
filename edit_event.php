<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not logged in');
}

require 'db.php';
if ($mysqli->connect_errno) die('DB error');

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$date = trim($_POST['event_date'] ?? '');
$time = trim($_POST['event_time'] ?? '');
$people = trim($_POST['people'] ?? '');

if ($id && $title && $date && $time && $people) {
    $stmt = $mysqli->prepare(
        "UPDATE events SET title=?, event_date=?, event_time=?, people=? WHERE id=? AND created_by=?"
    );
    $stmt->bind_param('ssssii', $title, $date, $time, $people, $id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

$mysqli->close();
echo 'Success';
?>