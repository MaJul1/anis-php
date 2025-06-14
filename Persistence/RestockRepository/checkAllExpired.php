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
    // Use the id directly
    $id = isset($item['id']) ? (int)$item['id'] : 0;
    if ($id <= 0) {
        $success = false;
        $error = 'Invalid restock detail id.';
        continue;
    }
    // Update RestockDetail only if the parent restock belongs to the user
    $sql = "UPDATE RestockDetail rd JOIN Restock r ON rd.RestockId = r.Id SET rd.IsExpiredChecked=TRUE WHERE rd.Id=$id AND rd.IsExpiredChecked=FALSE AND r.OwnerId=$userId";
    if (!$conn->query($sql)) {
        $success = false;
        $error = 'Failed to update some records.';
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $error]);
}
