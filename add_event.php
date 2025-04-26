<?php
session_name('CALENDARSESSID');
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$uid = $_SESSION['user_id'];

require 'db.php';
if ($mysqli->connect_errno) die('DB error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['event_date'] ?? '');
    $time = trim($_POST['event_time'] ?? '');
    $people = trim($_POST['people'] ?? '');

    if ($title && $date && $time) {
        $stmt = $mysqli->prepare(
            "INSERT INTO events (title, event_date, event_time, people, created_by)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssi', $title, $date, $time, $people, $uid);
        $stmt->execute();
        $stmt->close();

        header('Location: showcalendar_inJS.html?added=1');
        exit;
    }
}
?>
