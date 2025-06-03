<?php
include __DIR__ . '/../dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    $errors = [];
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $qpu = trim($_POST['qpu'] ?? '');
    $expWarn = trim($_POST['expWarn'] ?? '');
    $stockWarn = trim($_POST['stockWarn'] ?? '');

    // Validation
    if ($name === '') $errors[] = 'Product name is required.';
    if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = 'Valid price is required.';
    if ($unit === '') $errors[] = 'Product unit is required.';
    if ($qpu === '' || !is_numeric($qpu) || $qpu < 0) $errors[] = 'Valid quantity per unit is required.';
    if ($expWarn === '' || !is_numeric($expWarn) || $expWarn < 0) $errors[] = 'Valid expiration warning threshold is required.';
    if ($stockWarn === '' || !is_numeric($stockWarn) || $stockWarn < 0) $errors[] = 'Valid stock warning threshold is required.';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO Product (Name, Price, QuantityPerUnit, Unit, ExpirationWarningThreshold, OutOfStockWarningThreshold) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sdssii', $name, $price, $qpu, $unit, $expWarn, $stockWarn);
        if ($stmt->execute()) {
            header('Location: /anis-php/product.php');
            exit();
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>