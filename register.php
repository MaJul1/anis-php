<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
  <link rel="icon" type="image/png" href="assets/logo.png">
</head>
<body class="bg-light">
  <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow-sm p-4" style="min-width: 380px; max-width: 400px; width: 100%;">
      <h2 class="text-center mb-3">Register</h2>
      <?php
      session_start();
      require_once __DIR__ . '/Persistence/dbconn.php';
      $error = '';
      $success = '';
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $username = trim($_POST['username'] ?? '');
          $password = $_POST['password'] ?? '';
          if ($username === '' || $password === '') {
              $error = 'Username and password required.';
          } else {
              // Check if username exists
              $stmt = $conn->prepare('SELECT Id FROM `User` WHERE Username = ? LIMIT 1');
              $stmt->bind_param('s', $username);
              $stmt->execute();
              $stmt->store_result();
              if ($stmt->num_rows > 0) {
                  $error = 'Username already exists.';
              } else {
                  $hash = password_hash($password, PASSWORD_DEFAULT);
                  $stmt = $conn->prepare('INSERT INTO `User` (Username, Password) VALUES (?, ?)');
                  $stmt->bind_param('ss', $username, $hash);
                  if ($stmt->execute()) {
                      $success = 'Registration successful! You can now <a href="login.php">login</a>.';
                  } else {
                      $error = 'Registration failed. Please try again.';
                  }
              }
              $stmt->close();
          }
      }
      ?>
      <form action="register.php" method="POST">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input id="username" name="username" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input id="password" name="password" type="password" class="form-control" required>
        </div>
        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success" role="alert">
            <?= $success ?>
          </div>
        <?php endif; ?>
        <div class="mb-3">
          <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
          <a href="login.php" class="btn btn-secondary w-100">Cancel</a>
        </div>
      </form>
    </div>
  </div>     
</body>
</html>