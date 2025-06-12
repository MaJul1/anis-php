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
    // Delete all user-owned products (and cascade deletes for related restocks/stockouts if set in DB)
    $conn->query("DELETE FROM Product WHERE OwnerId = $userId");
    // Delete all restocks and details
    $conn->query("DELETE FROM Restock WHERE OwnerId = $userId");
    // Delete all stockouts and details
    $conn->query("DELETE FROM stockout WHERE OwnerId = $userId");
    // Delete the user
    $conn->query("DELETE FROM User WHERE Id = $userId");
    $conn->commit();
    session_destroy();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
}
