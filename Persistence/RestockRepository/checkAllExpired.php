<?php
// checkAllExpired.php: Set IsExpiredChecked=TRUE for all expired restock details in the dashboard table
include_once __DIR__ . '/../dbconn.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

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
    // Get ProductId and check ownership
    $sql = "SELECT Id FROM Product WHERE CONCAT(Name, ', ', QuantityPerUnit, ', ', Unit, ', ', FORMAT(Price, 2)) = '$name' AND OwnerId = $userId LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $productId = $row['Id'];
        // Update RestockDetail only if the parent restock belongs to the user
        $sql2 = "UPDATE RestockDetail rd JOIN Restock r ON rd.RestockId = r.Id SET rd.IsExpiredChecked=TRUE WHERE rd.ProductId=$productId AND rd.ExpirationDate='$expiration' AND rd.Count=$count AND rd.IsExpiredChecked=FALSE AND r.OwnerId=$userId LIMIT 1";
        if (!$conn->query($sql2)) {
            $success = false;
            $error = 'Failed to update some records.';
        }
    } else {
        $success = false;
        $error = 'Product not found or unauthorized: ' . $name;
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $error]);
}
