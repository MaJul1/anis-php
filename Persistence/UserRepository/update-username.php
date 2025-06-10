<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $userId = $_SESSION['user_id'];
    if ($newUsername === '') {
        header('Location: user.php?error=empty');
        exit;
    }
    // Check if username already exists
    $stmt = $conn->prepare('SELECT Id FROM `User` WHERE Username = ? AND Id != ? LIMIT 1');
    $stmt->bind_param('si', $newUsername, $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header('Location: user.php?error=exists');
        exit;
    }
    $stmt->close();
    // Update username
    $stmt = $conn->prepare('UPDATE `User` SET Username = ? WHERE Id = ?');
    $stmt->bind_param('si', $newUsername, $userId);
    if ($stmt->execute()) {
        $_SESSION['username'] = $newUsername;
        header('Location: ../../user.php?success=1');
        exit;
    } else {
        header('Location: user.php?error=update');
        exit;
    }
}
header('Location: user.php');
exit;
