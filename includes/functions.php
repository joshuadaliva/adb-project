<?php
require_once __DIR__ . '/../config/database.php';

// Sanitize input data
function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags($conn->real_escape_string(trim($data))));
}

// Redirect with message
function redirectWithMessage($url, $type, $message) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Check if user is student
function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

// Get user data
function getUserData($id, $role) {
    global $conn;
    
    if ($role === 'admin') {
        $table = 'admins';
        $idField = 'adminID';
    } else {
        $table = 'students';
        $idField = 'studentID';
    }
    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE $idField = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?>