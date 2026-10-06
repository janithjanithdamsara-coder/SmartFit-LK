<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Admin authentication required.']);
    exit;
}

try {
    $db = Database::getConnection();
    $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid Clothing Item ID']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM clothing_items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Clothing item removed successfully!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}