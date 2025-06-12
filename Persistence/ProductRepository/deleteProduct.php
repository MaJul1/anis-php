<?php
session_start();
include_once __DIR__ . '/../dbconn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
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
    $stmt = $conn->prepare("DELETE FROM Product WHERE Id = ?");
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    echo $success ? 'success' : 'fail';
    exit();
}
?>
