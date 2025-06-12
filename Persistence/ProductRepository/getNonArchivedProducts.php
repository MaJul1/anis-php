<?php
session_start();
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT Id AS ProductID, Name AS ProductName FROM Product WHERE Archived = 0 AND OwnerId = ? ORDER BY Name ASC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch products.']);
}
