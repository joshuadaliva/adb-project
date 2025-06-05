<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Room ID is required']));
}

$roomID = (int)$_GET['id'];

// Check active and pending bookings
$stmt = $conn->prepare("SELECT COUNT(*) as active_bookings 
                       FROM bookings 
                       WHERE roomID = ? AND (status = 'Active' OR status = 'Pending')");
$stmt->bind_param("i", $roomID);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($data);