<?php
require_once '../../Persistence/dbconn.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$userId = $_SESSION['user_id'];

$restockId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($restockId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid restock ID.']);
    exit;
}

// Ownership check
$sql = "SELECT 1 FROM Restock WHERE Id = ? AND OwnerId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $restockId, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}
$stmt->close();

$sql = "SELECT r.CreatedDate, p.Name AS ProductName, p.Id AS ProductId, rd.ExpirationDate, rd.Count, rd.Id AS RestockDetailId
        FROM Restock r
        JOIN RestockDetail rd ON r.Id = rd.RestockId
        JOIN Product p ON rd.ProductId = p.Id
        WHERE r.Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $restockId);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (count($rows) === 0) {
    echo json_encode(['success' => false, 'message' => 'No details found.']);
    exit;
}

$createdDate = $rows[0]['CreatedDate'];
$details = array_map(function($row) {
    return [
        'ProductName' => $row['ProductName'],
        'ProductId' => $row['ProductId'],
        'ExpirationDate' => $row['ExpirationDate'],
        'Count' => $row['Count'],
        'RestockDetailId' => $row['RestockDetailId']
    ];
}, $rows);

echo json_encode([
    'success' => true,
    'createdDate' => $createdDate,
    'details' => $details
]);
