<?php
include_once __DIR__ . '/../dbconn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    $stmt = $conn->prepare("UPDATE Product SET CurrentStockNumber = ? WHERE Id = ?");
    $stmt->bind_param('ii', $stock, $id);
    $success = $stmt->execute();
    $stmt->close();
    echo $success ? 'success' : 'fail';
    exit();
}
?>
