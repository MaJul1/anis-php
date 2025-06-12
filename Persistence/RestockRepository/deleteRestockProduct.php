<?php
require_once '../../Persistence/dbconn.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$restockDetailId = isset($data['restock_detail_id']) ? intval($data['restock_detail_id']) : 0;

if ($restockDetailId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid restock detail ID.']);
    exit;
}

// Get product id, count, restock id, and check ownership
$stmt = $conn->prepare("SELECT rd.ProductId, rd.Count, rd.RestockId FROM RestockDetail rd JOIN Restock r ON rd.RestockId = r.Id WHERE rd.Id = ? AND r.OwnerId = ?");
$stmt->bind_param('ii', $restockDetailId, $userId);
$stmt->execute();
$stmt->bind_result($productId, $count, $restockId);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Restock detail not found or unauthorized.']);
    exit;
}
$stmt->close();

try {
    $conn->begin_transaction();
    // Subtract count from product stock, but do not allow negative stock (ownership check)
    $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = GREATEST(CurrentStockNumber - ?, 0) WHERE Id = ? AND OwnerId = ?");
    $update->bind_param('iii', $count, $productId, $userId);
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
        // Delete the restock itself (ownership check)
        $delRestock = $conn->prepare("DELETE FROM Restock WHERE Id = ? AND OwnerId = ?");
        $delRestock->bind_param('ii', $restockId, $userId);
        $delRestock->execute();
        $delRestock->close();
    }
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
