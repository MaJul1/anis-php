<?php
include_once __DIR__ . '/../dbconn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM Product WHERE Id = ?");
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    echo $success ? 'success' : 'fail';
    exit();
}
?>
