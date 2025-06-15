<?php
// update-password.php: Handles password update for logged-in user
session_start();
include_once __DIR__ . '/Persistence/dbconn.php';

header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $retype = $_POST['retype_password'] ?? '';

    // Basic validation
    if (empty($current) || empty($new) || empty($retype)) {
        $error = 'All fields are required.';
    } elseif ($new !== $retype) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch current password hash
        $stmt = $conn->prepare('SELECT Password FROM `User` WHERE Id = ? AND IsDeleted = 0');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($hash);
        if ($stmt->fetch()) {
            // Verify current password
            if (password_verify($current, $hash)) {
                // Update to new password (hash it)
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt->close();
                $stmt2 = $conn->prepare('UPDATE `User` SET Password = ? WHERE Id = ?');
                $stmt2->bind_param('si', $newHash, $userId);
                if ($stmt2->execute()) {
                    $success = 'Password updated successfully!';
                } else {
                    $error = 'Failed to update password.';
                }
                $stmt2->close();
            } else {
                $error = 'Current password is incorrect.';
                $stmt->close();
            }
        } else {
            $error = 'User not found.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <title>Update Password</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5 bg-body-secondary">
    <div class="card mx-auto bg-body text-body" style="max-width:400px;">
        <div class="card-body">
            <h3 class="card-title mb-3">Update Password</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
            <?php endif; ?>
            <a href="user.php" class="btn btn-secondary mt-2">Back to Profile</a>
        </div>
    </div>
    <script>
      // Optional: Set theme based on cookie for Bootstrap 5.3+
      if(document.cookie.includes('theme=dark')) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
      } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
      }
    </script>
</body>
</html>
