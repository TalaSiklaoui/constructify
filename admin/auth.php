<?php

// Start a session if one is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect visitors who are not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}