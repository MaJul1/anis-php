<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User</title>
  <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
  <link rel="icon" type="image/png" href="assets/logo.png">
</head>
<body>
  <!-- modal-for-updating-username -->
   <div class="modal fade" id="update-username">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <span class="fs-3 fw-semibold">Update Username</span>
        </div>
        <form action="Persistence/UserRepository/update-username.php" method="post">
          <div class="modal-body">
            <div class="mb-3">
              <label for="username">New Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <a class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
          </div>
        </form>
      </div>
    </div>
   </div>

   <!-- modal for updating password -->
    <div class="modal fade" id="update-password">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Update Password</span>
          </div>
          <form action="update-password.php" method="POST" id="update-password-form">
            <div class="modal-body">
              <div class="mb-3">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="retype-password">Retype Password</label>
                <input type="password" id="retype-password" name="retype_password" class="form-control" required>
              </div>
              <div id="password-error" class="text-danger mb-2" style="display:none;"></div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save</button>
              <a class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.getElementById('update-password-form').addEventListener('submit', function(e) {
        var newPass = document.getElementById('new-password').value;
        var retypePass = document.getElementById('retype-password').value;
        var errorDiv = document.getElementById('password-error');
        if (newPass !== retypePass) {
          e.preventDefault();
          errorDiv.textContent = 'New passwords do not match!';
          errorDiv.style.display = 'block';
        } else {
          errorDiv.style.display = 'none';
        }
      });
    </script>
  <!-- sidebar (mobile toggle) -->
    <div class="d-flex ps-3 d-md-none pt-1 border-bottom position-fixed" style="height: 50px; background-color: var(--bs-secondary-bg); width: 100%">
      <a class="me-auto" data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded fs-2 ps-2 pe-2 link-body-emphasis"></i>
      </a>
      <a href="user.php">
        <i class="bi bi-person-circle fs-1 me-3 link-body-emphasis"></i>
      </a>
    </div>
    <div class="d-md-none" style="height: 50px;"></div>

    <div class="d-flex">
      <div id="sidebar"></div>
      <!-- body -->
      <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column" style="max-width: 450px;">
        <h1>User Information</h1>
        <span>Username</span>
        <div class="input-group mb-3">
          <input type="text" class="form-control" aria-label="Username of the User" aria-describedby="username-input" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" disabled>
          <a class="btn btn-outline-secondary" type="button" id="username-input" data-bs-toggle="modal" href="#update-username"><i class="bi bi-pencil-square"></i></a>
        </div>
        <span>Password</span>
        <div class="input-group mb-3">
          <input type="password" class="form-control" aria-label="Password of the User" aria-describedby="password-input" value="SamplePassword" disabled>
          <a class="btn btn-outline-secondary" type="button" id="password-input" data-bs-toggle="modal" href="#update-password"><i class="bi bi-pencil-square"></i></a>
        </div>
        <button class="btn btn-primary w-100 mb-3" onclick="window.location.href='logout.php'">Logout</button>
        <button class="btn btn-danger w-100" id="delete-account-btn">Delete Account</button>
      </div>
    </div>
    <script>
      window.activeSidebar = 'user';
      window.sidebarUsername = "<?= htmlspecialchars($_SESSION['username'] ?? '') ?>";
    </script>
    <script src="js/sidebar.js"></script>
    <script src="js/user.js"></script>
</body>
</html>