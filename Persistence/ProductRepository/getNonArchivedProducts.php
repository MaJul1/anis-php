<?php
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT Id AS ProductID, Name AS ProductName FROM Product WHERE Archived = 0 ORDER BY Name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch products.']);
}
