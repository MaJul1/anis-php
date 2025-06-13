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
if (!isset($data['products']) || !is_array($data['products']) || count($data['products']) === 0) {
    echo json_encode(['success' => false, 'message' => 'No products provided.']);
    exit;
}

try {
    $conn->begin_transaction();
    $restockDate = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO Restock (CreatedDate, OwnerId) VALUES (?, ?)");
    $stmt->bind_param('si', $restockDate, $userId);
    $stmt->execute();
    $restockId = $conn->insert_id;
    $stmt->close();

    $insertDetail = $conn->prepare("INSERT INTO RestockDetail (RestockId, ProductId, ExpirationDate, Count) VALUES (?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE Product SET CurrentStockNumber = CurrentStockNumber + ? WHERE Id = ? AND OwnerId = ?");

    foreach ($data['products'] as $item) {
        $pid = $item['product_id'];
        $exp = $item['expiration_date'];
        $count = $item['count'];
        if (!$pid || !$count || $count < 1) {
            throw new Exception('Invalid product data.');
        }
        // Check product ownership
        $check = $conn->prepare("SELECT Id FROM Product WHERE Id = ? AND OwnerId = ?");
        $check->bind_param('ii', $pid, $userId);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            throw new Exception('Unauthorized product.');
        }
        $check->close();
        // Handle nullable expiration date
        if ($exp === null || $exp === '') {
            $expParam = null;
            $insertDetail->bind_param('iisi', $restockId, $pid, $expParam, $count);
        } else {
            $insertDetail->bind_param('iisi', $restockId, $pid, $exp, $count);
        }
        $insertDetail->execute();
        $updateStock->bind_param('iii', $count, $pid, $userId);
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
