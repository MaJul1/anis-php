<?php
require_once '../../Persistence/dbconn.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$restockId = isset($data['restock_id']) ? intval($data['restock_id']) : 0;
$productId = isset($data['product_id']) ? intval($data['product_id']) : 0;
$expiration = isset($data['expiration_date']) ? $data['expiration_date'] : null;
if ($expiration === '') $expiration = null;
$count = isset($data['count']) ? intval($data['count']) : 0;

if ($restockId <= 0 || $productId <= 0 || $count < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Ownership check: restock and product
$stmt = $conn->prepare("SELECT 1 FROM Restock WHERE Id = ? AND OwnerId = ?");
$stmt->bind_param('ii', $restockId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$stmt->close();
$stmt = $conn->prepare("SELECT 1 FROM Product WHERE Id = ? AND OwnerId = ?");
$stmt->bind_param('ii', $productId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized product.']);
    exit;
}
$stmt->close();

try {
    $conn->begin_transaction();
    // Insert into RestockDetail
    if ($expiration === null) {
        $stmt = $conn->prepare("INSERT INTO RestockDetail (RestockId, ProductId, ExpirationDate, Count) VALUES (?, ?, NULL, ?)");
        $stmt->bind_param('iii', $restockId, $productId, $count);
    } else {
        $stmt = $conn->prepare("INSERT INTO RestockDetail (RestockId, ProductId, ExpirationDate, Count) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iisi', $restockId, $productId, $expiration, $count);
    }
    $stmt->execute();
    $stmt->close();
    // Update Product stock
    $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ? AND OwnerId = ?");
    $update->bind_param('iii', $count, $productId, $userId);
    $update->execute();
    $update->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
