<?php
session_name('CALENDARSESSID');
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success'=>false,'theme'=>'light']);
  exit;
}

$mysqli = new mysqli('localhost','devpatel_admin','Patel9317$$','devpatel_calendar');
$stmt = $mysqli->prepare('SELECT theme FROM user_profiles WHERE user_id = ?');
$stmt->bind_param('i',$_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($theme);
$stmt->fetch();
$stmt->close();
$mysqli->close();

echo json_encode(['success'=>true,'theme'=>$theme]);








