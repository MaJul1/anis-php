<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User</title>
  <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <!-- sidebar -->
  <div class="d-flex bg-light ps-3 d-md-none pt-1" style="height: 50px;">
      <a data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded text-dark fs-2 ps-2 pe-2"></i>
      </a>
    </div>

    <div class="d-flex">
      <div class="d-flex flex-column offcanvas-md offcanvas-start p-3 bg-light position-fixed" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 235px; height: 100vh">
        <a class="d-flex align-items-center mb-0 mb-md-1 link-dark text-decoration-none" href="index.php">
          <div class="bg-secondary me-3 rounded" style="width: 40px; height: 40px"></div>
          <span class="fs-4 d-md-inline fw-semibold">ANIS</span>
        </a>
        <hr />
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="index.php">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
          </a>
        </div>
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="product.php">
            <i class="bi bi-box me-2"></i>
            Products
          </a>
        </div>
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="restock.php">
            <i class="bi bi-box-arrow-in-down me-2"></i>
            Restock
          </a>
        </div>
        <div class="mb-1 p-2 rounded mb-auto">
          <a class="text-dark text-decoration-none d-flex" href="stockout.php">
            <i class="bi bi-box-arrow-up me-2"></i>
            Stock Out
          </a>
        </div>
        <hr class="mb-1">
        <a href="#" class="d-flex align-items-center text-decoration-none text-dark ps-2 bg-primary text-white rounded">
          <i class="bi bi-person-circle fs-1 me-3"></i>
          Hello Majul
        </a>
      </div>
      <div class="d-none d-md-block" style="min-width: 235px; height: 100vh;"></div>

      <!-- body -->
      <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column" style="min-width: 400px; max-width: 450px;">
        <h1>User Information</h1>
        <span>Username</span>
        <div class="input-group mb-3">
          <input type="text" class="form-control" aria-label="Username of the User" aria-describedby="username-input" value="MaJul" disabled>
          <a class="btn btn-outline-secondary" type="button" id="username-input"><i class="bi bi-pencil-square"></i></a>
        </div>
        <span>Password</span>
        <div class="input-group mb-3">
          <input type="password" class="form-control" aria-label="Password of the User" aria-describedby="password-input" value="SamplePassword" disabled>
          <a class="btn btn-outline-secondary" type="button" id="password-input"><i class="bi bi-pencil-square"></i></a>
        </div>
        <button class="btn btn-primary w-100 mb-3">Logout</button>
        <button class="btn btn-danger w-100">Delete Account</button>
      </div>
    </div>
</body>
</html>