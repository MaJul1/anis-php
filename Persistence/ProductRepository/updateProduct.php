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
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $qpu = trim($_POST['qpu'] ?? '');
    $expWarn = trim($_POST['expWarn'] ?? '');
    $stockWarn = trim($_POST['stockWarn'] ?? '');
    $errors = [];
    if ($name === '') $errors[] = 'Product name is required.';
    if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = 'Valid price is required.';
    if ($unit === '') $errors[] = 'Product unit is required.';
    if ($qpu === '' || !is_numeric($qpu) || $qpu < 0) $errors[] = 'Valid quantity per unit is required.';
    if ($expWarn === '' || !is_numeric($expWarn) || $expWarn < 0) $errors[] = 'Valid expiration warning threshold is required.';
    if ($stockWarn === '' || !is_numeric($stockWarn) || $stockWarn < 0) $errors[] = 'Valid stock warning threshold is required.';
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE Product SET Name=?, Price=?, QuantityPerUnit=?, Unit=?, ExpirationWarningThreshold=?, OutOfStockWarningThreshold=? WHERE Id=?");
        $stmt->bind_param('sdssiii', $name, $price, $qpu, $unit, $expWarn, $stockWarn, $id);
        $success = $stmt->execute();
        $stmt->close();
        echo $success ? 'success' : 'fail';
        exit();
    } else {
        echo implode('\n', $errors);
        exit();
    }
}
?>
