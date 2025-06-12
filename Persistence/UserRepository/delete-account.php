<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];
require_once '../../Persistence/dbconn.php';

try {
    $conn->begin_transaction();
    // Mark user as deleted
    $conn->query("UPDATE User SET IsDeleted = 1 WHERE Id = $userId");
    $conn->commit();
    session_destroy();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
}
