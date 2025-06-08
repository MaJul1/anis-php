<?php
include_once __DIR__ . '/../dbconn.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$products = isset($data['products']) ? $data['products'] : [];
if (empty($products)) {
    http_response_code(400);
    echo json_encode(['error' => 'No products provided']);
    exit;
}

// Validate all products first
foreach ($products as $prod) {
    $productId = intval($prod['product_id']);
    $count = intval($prod['count']);
    if (!$productId || !$count) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product or count']);
        exit;
    }
    $checkStmt = $conn->prepare("SELECT Name, CurrentStockNumber FROM product WHERE Id = ?");
    $checkStmt->bind_param('i', $productId);
    $checkStmt->execute();
    $checkStmt->bind_result($productName, $currentStock);
    $checkStmt->fetch();
    $checkStmt->close();
    if ($currentStock < $count) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Not enough stock for ' . $productName . '. Current stock: ' . $currentStock,
            'product_name' => $productName,
            'current_stock' => $currentStock
        ]);
        exit;
    }
}

// Insert new stockout
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO stockout () VALUES ()");
    $stmt->execute();
    $stockoutId = $stmt->insert_id;
    $stmt->close();
    foreach ($products as $prod) {
        $productId = intval($prod['product_id']);
        $count = intval($prod['count']);
        $stmt = $conn->prepare("INSERT INTO stockoutdetail (StockOutCount, ProductId, StockOutId) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $count, $productId, $stockoutId);
        $stmt->execute();
        $stmt->close();
        // Decrease product stock
        $updateStmt = $conn->prepare("UPDATE product SET CurrentStockNumber = CurrentStockNumber - ? WHERE Id = ?");
        $updateStmt->bind_param('ii', $count, $productId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create stock out', 'details' => $e->getMessage()]);
}
$conn->close();
