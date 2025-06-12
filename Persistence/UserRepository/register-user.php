<?php
// register-user.php
require_once '../dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $retypePassword = $_POST['retype-password'] ?? '';
    $errors = [];

    // Validate input
    if (empty($username) || empty($password) || empty($retypePassword)) {
        $errors[] = 'All fields are required.';
    }
    if ($password !== $retypePassword) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    // Check if username exists
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT Id FROM User WHERE Username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Username already exists.';
        }
        $stmt->close();
    }

    // Register user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO User (Username, Password) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hashedPassword);
        if ($stmt->execute()) {
            header('Location: ../../login.php?register=success');
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }

    // Redirect back with errors
    if (!empty($errors)) {
        session_start();
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_username'] = $username;
        header('Location: ../../register.php');
        exit();
    }
} else {
    header('Location: ../../register.php');
    exit();
}
