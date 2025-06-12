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
$restockDetailId = isset($data['restock_detail_id']) ? intval($data['restock_detail_id']) : 0;
$newCount = isset($data['count']) ? intval($data['count']) : null;
$newProductId = isset($data['product_id']) ? intval($data['product_id']) : 0;
$newExpiration = isset($data['expiration_date']) ? $data['expiration_date'] : null;

if ($restockDetailId <= 0 || $newCount === null || $newProductId <= 0 || !$newExpiration) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Get the old count, product id, expiration, and restock id
$stmt = $conn->prepare("SELECT rd.Count, rd.ProductId, rd.ExpirationDate, rd.RestockId FROM RestockDetail rd JOIN Restock r ON rd.RestockId = r.Id WHERE rd.Id = ? AND r.OwnerId = ?");
$stmt->bind_param('ii', $restockDetailId, $userId);
$stmt->execute();
$stmt->bind_result($oldCount, $oldProductId, $oldExpiration, $restockId);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Restock detail not found or unauthorized.']);
    exit;
}
$stmt->close();

try {
    $conn->begin_transaction();
    // If product changed, adjust both old and new product stock
    if ($oldProductId != $newProductId) {
        // Subtract old count from old product (ownership check)
        $updateOld = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber - ? WHERE Id = ? AND OwnerId = ?");
        $updateOld->bind_param('iii', $oldCount, $oldProductId, $userId);
        $updateOld->execute();
        $updateOld->close();
        // Add new count to new product (ownership check)
        $updateNew = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ? AND OwnerId = ?");
        $updateNew->bind_param('iii', $newCount, $newProductId, $userId);
        $updateNew->execute();
        $updateNew->close();
    } else {
        // Same product, adjust by difference
        $diff = $newCount - $oldCount;
        $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ? AND OwnerId = ?");
        $update->bind_param('iii', $diff, $oldProductId, $userId);
        $update->execute();
        $update->close();
    }
    // Update restock detail
    $updateDetail = $conn->prepare("UPDATE RestockDetail SET ProductId = ?, ExpirationDate = ?, Count = ? WHERE Id = ?");
    $updateDetail->bind_param('isii', $newProductId, $newExpiration, $newCount, $restockDetailId);
    $updateDetail->execute();
    $updateDetail->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
