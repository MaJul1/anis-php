<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
  <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm p-4" style="min-width: 320px; max-width: 400px; width: 100%;">
      <h2 class="mb-4 text-center">Login</h2>
      <p class="text-center mb-4 text-secondary">Welcome to <span class="fw-semibold">Aling Nena's Inventory System (ANIS)</span></p>
      <form method="post" action="login.php">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
        <a href="#" class="btn btn-secondary w-100">Register</a>
      </form>
    </div>
  </div>
</body>
</html>