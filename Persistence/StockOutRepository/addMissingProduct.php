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
$count = isset($data['count']) ? intval($data['count']) : 0;

// Validate input
if (!$stockoutId || !$productId || !$count) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Ownership check: stockout and product
$stmt = $conn->prepare("SELECT 1 FROM stockout WHERE Id = ? AND OwnerId = ?");
$stmt->bind_param('ii', $stockoutId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    $stmt->close();
    exit;
}
$stmt->close();
$stmt = $conn->prepare("SELECT 1 FROM Product WHERE Id = ? AND OwnerId = ?");
$stmt->bind_param('ii', $productId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized product.']);
    $stmt->close();
    exit;
}
$stmt->close();

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

try {
    $conn->begin_transaction();
    // Insert into stockoutdetail (fix: use correct column name StockOutCount)
    $stmt = $conn->prepare("INSERT INTO stockoutdetail (StockOutId, ProductId, StockOutCount) VALUES (?, ?, ?)");
    $stmt->bind_param('iii', $stockoutId, $productId, $count);
    $stmt->execute();
    $stmt->close();
    // Update Product stock (ownership check)
    $update = $conn->prepare("UPDATE Product SET CurrentStockNumber = GREATEST(CurrentStockNumber - ?, 0) WHERE Id = ? AND OwnerId = ?");
    $update->bind_param('iii', $count, $productId, $userId);
    $update->execute();
    $update->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
