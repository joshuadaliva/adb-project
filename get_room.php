<?php
require_once __DIR__ . '/config/database.php';

$roomID = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT *, status as current_status FROM rooms WHERE roomID = ?");
$stmt->bind_param("i", $roomID);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: application/json');
echo json_encode($result->fetch_assoc());