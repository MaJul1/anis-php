<?php
session_start();
include_once __DIR__ . '/../dbconn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo 'unauthorized';
        exit();
    }
    // Validate ownership
    $stmt = $conn->prepare("SELECT OwnerId FROM Product WHERE Id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($ownerId);
    $stmt->fetch();
    $stmt->close();
    if ($ownerId != $userId) {
        echo 'unauthorized';
        exit();
    }
    $stmt = $conn->prepare("UPDATE Product SET CurrentStockNumber = ? WHERE Id = ?");
    $stmt->bind_param('ii', $stock, $id);
    $success = $stmt->execute();
    $stmt->close();
    echo $success ? 'success' : 'fail';
    exit();
}
?>
