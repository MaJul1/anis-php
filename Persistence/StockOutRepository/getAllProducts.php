<?php
include_once __DIR__ . '/../dbconn.php';
header('Content-Type: application/json');

$sql = "SELECT Id, Name FROM product WHERE Archived = 0";
$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$conn->close();
echo json_encode($products);
