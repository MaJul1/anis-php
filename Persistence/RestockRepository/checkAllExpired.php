<?php
// checkAllExpired.php: Set IsExpiredChecked=TRUE for all expired restock details in the dashboard table
include_once __DIR__ . '/../dbconn.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

$success = true;
$error = '';

foreach ($data['items'] as $item) {
    // Find the RestockDetail row by product name, expiration date, and count
    $name = $conn->real_escape_string($item['name']);
    $expiration = date('Y-m-d', strtotime($item['expiration']));
    $count = (int)$item['count'];
    // Get ProductId
    $sql = "SELECT Id FROM Product WHERE CONCAT(Name, ', ', QuantityPerUnit, ', ', Unit, ', ', FORMAT(Price, 2)) = '$name' LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $productId = $row['Id'];
        // Update RestockDetail
        $sql2 = "UPDATE RestockDetail SET IsExpiredChecked=TRUE WHERE ProductId=$productId AND ExpirationDate='$expiration' AND Count=$count AND IsExpiredChecked=FALSE LIMIT 1";
        if (!$conn->query($sql2)) {
            $success = false;
            $error = 'Failed to update some records.';
        }
    } else {
        $success = false;
        $error = 'Product not found: ' . $name;
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $error]);
}
