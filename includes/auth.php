<?php
session_start();
require_once __DIR__ . '/functions.php';

// Check if user is logged in and redirect if necessary
if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'signup.php') {
    if (!isLoggedIn()) {
        redirectWithMessage('/adb/login.php', 'error', 'Please login first');
    }
}
?>