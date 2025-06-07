<?php
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$restockId = isset($data['restock_id']) ? intval($data['restock_id']) : 0;
$productId = isset($data['product_id']) ? intval($data['product_id']) : 0;
$expiration = isset($data['expiration_date']) ? $data['expiration_date'] : null;
$count = isset($data['count']) ? intval($data['count']) : 0;

if ($restockId <= 0 || $productId <= 0 || !$expiration || $count < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    $conn->begin_transaction();
    // Insert into RestockDetail
    $stmt = $conn->prepare("INSERT INTO RestockDetail (RestockId, ProductId, ExpirationDate, Count) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iisi', $restockId, $productId, $expiration, $count);
    $stmt->execute();
    $stmt->close();
    // Update Product stock
    $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ?");
    $update->bind_param('ii', $count, $productId);
    $update->execute();
    $update->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
