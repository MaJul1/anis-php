<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
include_once __DIR__ . '/../dbconn.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$sql = "SELECT Id, Name FROM product WHERE Archived = 0 AND OwnerId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($products);
