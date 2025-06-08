<?php
include_once __DIR__ . '/../dbconn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$stockoutId = isset($data['stockout_id']) ? intval($data['stockout_id']) : 0;
$productId = isset($data['product_id']) ? intval($data['product_id']) : 0;
$count = isset($data['count']) ? intval($data['count']) : 0;

if (!$stockoutId || !$productId || !$count) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Check if product has enough stock
$checkStmt = $conn->prepare("SELECT CurrentStockNumber FROM product WHERE Id = ?");
$checkStmt->bind_param('i', $productId);
$checkStmt->execute();
$checkStmt->bind_result($currentStock);
$checkStmt->fetch();
$checkStmt->close();

if ($currentStock < $count) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Not enough stock for this product.',
        'current_stock' => $currentStock
    ]);
    $conn->close();
    exit;
}

// Insert new stockoutdetail
$stmt = $conn->prepare("INSERT INTO stockoutdetail (StockOutCount, ProductId, StockOutId) VALUES (?, ?, ?)");
$stmt->bind_param('iii', $count, $productId, $stockoutId);
$success = $stmt->execute();

if ($success) {
    // Decrease the product stock count
    $updateStmt = $conn->prepare("UPDATE product SET CurrentStockNumber = CurrentStockNumber - ? WHERE Id = ?");
    $updateStmt->bind_param('ii', $count, $productId);
    $updateStmt->execute();
    $updateStmt->close();
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add missing product']);
}
$stmt->close();
$conn->close();
