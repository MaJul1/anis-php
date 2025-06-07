<?php
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

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

// Get the old count, product id, and expiration
$stmt = $conn->prepare("SELECT Count, ProductId, ExpirationDate FROM RestockDetail WHERE Id = ?");
$stmt->bind_param('i', $restockDetailId);
$stmt->execute();
$stmt->bind_result($oldCount, $oldProductId, $oldExpiration);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Restock detail not found.']);
    exit;
}
$stmt->close();

try {
    $conn->begin_transaction();
    // If product changed, adjust both old and new product stock
    if ($oldProductId != $newProductId) {
        // Subtract old count from old product
        $updateOld = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber - ? WHERE Id = ?");
        $updateOld->bind_param('ii', $oldCount, $oldProductId);
        $updateOld->execute();
        $updateOld->close();
        // Add new count to new product
        $updateNew = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ?");
        $updateNew->bind_param('ii', $newCount, $newProductId);
        $updateNew->execute();
        $updateNew->close();
    } else {
        // Same product, adjust by difference
        $diff = $newCount - $oldCount;
        $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ?");
        $update->bind_param('ii', $diff, $oldProductId);
        $update->execute();
        $update->close();
    }
    // Update RestockDetail (product, expiration, count)
    $stmt = $conn->prepare("UPDATE RestockDetail SET ProductId = ?, ExpirationDate = ?, Count = ? WHERE Id = ?");
    $stmt->bind_param('isii', $newProductId, $newExpiration, $newCount, $restockDetailId);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
