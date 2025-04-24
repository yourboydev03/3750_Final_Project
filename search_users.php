<?php
session_name('CALENDARSESSID');
session_start();
header('Content-Type: application/json');

// Only for logged-in users
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Connect
$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
if ($mysqli->connect_errno) {
    echo json_encode([]);
    exit;
}

// Prefix search
$param = $q . '%';
$stmt = $mysqli->prepare(
    'SELECT u.id, p.display_name
       FROM user_profiles p
       JOIN users u ON u.id = p.user_id
      WHERE p.display_name LIKE ?
      ORDER BY p.display_name
      LIMIT 10'
);
if (!$stmt) {
    echo json_encode([]);
    exit;
}
$stmt->bind_param('s', $param);
$stmt->execute();
$stmt->bind_result($id, $name);

$results = [];
while ($stmt->fetch()) {
    $results[] = ['id' => $id, 'display_name' => $name];
}
$stmt->close();
$mysqli->close();

echo json_encode($results);








