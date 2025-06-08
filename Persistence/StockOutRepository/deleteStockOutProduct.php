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

if (!$stockoutId || !$productId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Get the StockOutCount for this stockoutdetail
$stmt = $conn->prepare("SELECT Id, StockOutCount FROM stockoutdetail WHERE StockOutId = ? AND ProductId = ?");
$stmt->bind_param('ii', $stockoutId, $productId);
$stmt->execute();
$stmt->bind_result($detailId, $stockOutCount);
if ($stmt->fetch()) {
    $stmt->close();
    // Delete the stockoutdetail
    $delStmt = $conn->prepare("DELETE FROM stockoutdetail WHERE Id = ?");
    $delStmt->bind_param('i', $detailId);
    $delStmt->execute();
    $delStmt->close();
    // Add the stock back to the product
    $updStmt = $conn->prepare("UPDATE product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ?");
    $updStmt->bind_param('ii', $stockOutCount, $productId);
    $updStmt->execute();
    $updStmt->close();
    // Check if this was the last product in the stockout
    $checkLast = $conn->prepare("SELECT COUNT(*) FROM stockoutdetail WHERE StockOutId = ?");
    $checkLast->bind_param('i', $stockoutId);
    $checkLast->execute();
    $checkLast->bind_result($remaining);
    $checkLast->fetch();
    $checkLast->close();
    if ($remaining == 0) {
        // Delete the stockout itself
        $delStockout = $conn->prepare("DELETE FROM stockout WHERE Id = ?");
        $delStockout->bind_param('i', $stockoutId);
        $delStockout->execute();
        $delStockout->close();
    }
    echo json_encode(['success' => true]);
} else {
    $stmt->close();
    http_response_code(404);
    echo json_encode(['error' => 'Stock out detail not found']);
}
$conn->close();
