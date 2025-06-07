<?php
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$restockDetailId = isset($data['restock_detail_id']) ? intval($data['restock_detail_id']) : 0;

if ($restockDetailId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid restock detail ID.']);
    exit;
}

// Get product id, count, and restock id
$stmt = $conn->prepare("SELECT ProductId, Count, RestockId FROM RestockDetail WHERE Id = ?");
$stmt->bind_param('i', $restockDetailId);
$stmt->execute();
$stmt->bind_result($productId, $count, $restockId);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Restock detail not found.']);
    exit;
}
$stmt->close();

try {
    $conn->begin_transaction();
    // Subtract count from product stock, but do not allow negative stock
    $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = GREATEST(CurrentStockNumber - ?, 0) WHERE Id = ?");
    $update->bind_param('ii', $count, $productId);
    $update->execute();
    $update->close();
    // Delete the restock detail
    $del = $conn->prepare("DELETE FROM RestockDetail WHERE Id = ?");
    $del->bind_param('i', $restockDetailId);
    $del->execute();
    $del->close();
    // Check if this was the last product in the restock
    $check = $conn->prepare("SELECT COUNT(*) FROM RestockDetail WHERE RestockId = ?");
    $check->bind_param('i', $restockId);
    $check->execute();
    $check->bind_result($remaining);
    $check->fetch();
    $check->close();
    if ($remaining == 0) {
        // Delete the restock itself
        $delRestock = $conn->prepare("DELETE FROM Restock WHERE Id = ?");
        $delRestock->bind_param('i', $restockId);
        $delRestock->execute();
        $delRestock->close();
    }
    $conn->commit();
    echo json_encode(['success' => true, 'deletedRestock' => $remaining == 0]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
