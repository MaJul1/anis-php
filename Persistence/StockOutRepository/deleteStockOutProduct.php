<?php
include_once __DIR__ . '/../dbconn.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

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
$stmt = $conn->prepare("SELECT sd.Id, sd.StockOutCount, s.OwnerId FROM stockoutdetail sd JOIN stockout s ON sd.StockOutId = s.Id WHERE sd.StockOutId = ? AND sd.ProductId = ? AND s.OwnerId = ?");
$stmt->bind_param('iii', $stockoutId, $productId, $userId);
$stmt->execute();
$stmt->bind_result($detailId, $stockOutCount, $ownerId);
if ($stmt->fetch()) {
    $stmt->close();
    try {
        $conn->begin_transaction();
        // Add the stock back to the product (ownership check)
        $updStmt = $conn->prepare("UPDATE product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ? AND OwnerId = ?");
        $updStmt->bind_param('iii', $stockOutCount, $productId, $userId);
        $updStmt->execute();
        $updStmt->close();
        // Delete the stockoutdetail
        $delStmt = $conn->prepare("DELETE FROM stockoutdetail WHERE Id = ?");
        $delStmt->bind_param('i', $detailId);
        $delStmt->execute();
        $delStmt->close();
        // Check if this was the last product in the stockout
        $checkLast = $conn->prepare("SELECT COUNT(*) FROM stockoutdetail WHERE StockOutId = ?");
        $checkLast->bind_param('i', $stockoutId);
        $checkLast->execute();
        $checkLast->bind_result($remaining);
        $checkLast->fetch();
        $checkLast->close();
        if ($remaining == 0) {
            // Delete the stockout itself (ownership check)
            $delStockout = $conn->prepare("DELETE FROM stockout WHERE Id = ? AND OwnerId = ?");
            $delStockout->bind_param('ii', $stockoutId, $userId);
            $delStockout->execute();
            $delStockout->close();
        }
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    $stmt->close();
    http_response_code(404);
    echo json_encode(['error' => 'Stock out detail not found or unauthorized.']);
}
$conn->close();
