<?php
session_name('CALENDARSESSID');
session_start();
header('Content-Type: application/json');

// If not logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $mysqli->prepare(
    'SELECT profile_pic 
       FROM user_profiles 
      WHERE user_id = ?'
) or die(json_encode(['success' => false]));
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($pic);

if ($stmt->fetch()) {
    // If no custom pic, use placeholder
    $url = $pic
        ? "uploads/{$pic}"
        : "images/placeholder.png";
    echo json_encode(['success' => true, 'profile_pic' => $url]);
} else {
    echo json_encode(['success' => false]);
}
$stmt->close();
$mysqli->close();








