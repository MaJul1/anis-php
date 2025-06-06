<?php
require_once '../../Persistence/dbconn.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['products']) || !is_array($data['products']) || count($data['products']) === 0) {
    echo json_encode(['success' => false, 'message' => 'No products provided.']);
    exit;
}

try {
    $conn->begin_transaction();
    $restockDate = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO Restock (CreatedDate) VALUES (?)");
    $stmt->bind_param('s', $restockDate);
    $stmt->execute();
    $restockId = $conn->insert_id;
    $stmt->close();

    $insertDetail = $conn->prepare("INSERT INTO RestockDetail (RestockId, ProductId, ExpirationDate, Count) VALUES (?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ?");

    foreach ($data['products'] as $item) {
        $pid = $item['product_id'];
        $exp = $item['expiration_date'];
        $count = $item['count'];
        if (!$pid || !$exp || !$count || $count < 1) {
            throw new Exception('Invalid product data.');
        }
        $insertDetail->bind_param('iisi', $restockId, $pid, $exp, $count);
        $insertDetail->execute();
        $updateStock->bind_param('ii', $count, $pid);
        $updateStock->execute();
    }
    $insertDetail->close();
    $updateStock->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
