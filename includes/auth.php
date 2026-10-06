<?php
// SmartFit AI - Admin Auth Guard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}