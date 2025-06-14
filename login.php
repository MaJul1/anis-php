<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/Persistence/dbconn.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username and password required.';
    } else {
        $stmt = $conn->prepare('SELECT Id, Password, IsDeleted FROM `User` WHERE Username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $error = 'Invalid username or password.';
        } else {
            $stmt->bind_result($userId, $hash, $isDeleted);
            $stmt->fetch();
            if ($isDeleted) {
                $error = 'Account is deleted.';
            } elseif (!password_verify($password, $hash)) {
                $error = 'Invalid username or password.';
            } else {
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                header('Location: index.php');
                exit();
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="image/png" href="assets/logo.png">
</head>
<body class="bg-light">
  <div class="container d-flex flex-column justify-content-md-center mt-4 mt-md-0 align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm p-4" style="min-width: 320px; max-width: 400px; width: 100%;">
      <img src="assets/logo.png" alt="" style="max-width: 150px;" class="align-self-center">
      <h2 class="mb-3 text-center">ANIS</h2>
      <p class="text-center mb-4 text-secondary">Welcome to <span class="fw-semibold">Aling Nena's Inventory System</span></p>
      <form method="post" action="login.php">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
          <div class="alert alert-success" role="alert">
            Registration successful! You can now log in.
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
        <a href="register.php" class="btn btn-secondary w-100">Register</a>
      </form>
    </div>
  </div>
</body>
</html>