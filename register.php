<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
  <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow-sm p-4" style="min-width: 380px; max-width: 400px; width: 100%;">
      <h2 class="text-center mb-3">Register</h2>
      <form action="register.php">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input id="username" type="text" class="form-control">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input id="password" type="text" class="form-control">
        </div>
        <div class="mb-3">
          <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
          <a href="#" class="btn btn-secondary w-100">Cancel</a>
        </div>
      </form>
    </div>
  </div>     
</body>
</html>