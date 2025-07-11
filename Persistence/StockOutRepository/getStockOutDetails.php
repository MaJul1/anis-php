<?php
include_once __DIR__ . '/../dbconn.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

if (!isset($_GET['stockout_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing stockout_id']);
    exit;
}

$stockoutId = intval($_GET['stockout_id']);

// Ownership check
$sql = "SELECT 1 FROM stockout WHERE Id = ? AND OwnerId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $stockoutId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$stmt->close();

$sql = "SELECT p.Name, sd.StockOutCount, sd.ProductId
        FROM stockoutdetail sd
        JOIN product p ON sd.ProductId = p.Id
        WHERE sd.StockOutId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $stockoutId);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($products);
