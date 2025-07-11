<?php

  session_start();
  $id = $_SESSION["user_id"];
  if($id === null)
  {
    header("location: login.php");
    exit;
  }

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="image/png" href="assets/logo.png">
  </head>

  <body>

    <!-- sidebar (mobile toggle) -->
    <div class="d-flex ps-3 d-md-none pt-1 border-bottom position-fixed" style="height: 50px; background-color: var(--bs-secondary-bg); width: 100%">
      <a class="me-auto" data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded fs-2 ps-2 pe-2 link-body-emphasis"></i>
      </a>
      <a href="user.php">
        <i class="bi bi-person-circle fs-1 me-3 link-body-emphasis"></i>
      </a>
    </div>
    <div style="height: 50px;" class="d-md-none"></div>

    <div class="d-flex">
      <div id="sidebar"></div>
      <!-- body -->
      <div class="container ms-lg-4 p-md-5 pt-3 d-flex flex-column">
        <span class="fs-1 fw-bold mb-3">Dashboard</span>
        <div class="row row-gap-3 mb-3">
          <div class="col-xl-3 col-6">
            <a href="product.php" class="text-decoration-none text-dark">
              <div class="d-flex flex-column align-items-end border border-1 p-3 bg-success rounded-3 text-white h-100">
                <span class="fs-3 fw-bold">Total Products</span>
                <span class="fs-1 fw-bold">
                  <?php
                  include_once __DIR__ . '/Persistence/dbconn.php';
                  $userId = $_SESSION['user_id'];
                  $result = $conn->query("SELECT COUNT(*) AS total FROM Product WHERE Archived = FALSE AND OwnerId = $userId");
                  $row = $result->fetch_assoc();
                  echo $row['total'];
                  ?>
                </span>
              </div>
            </a>
          </div>
          <div class="col-xl-3 col-6">
            <a href="restock.php" class="text-decoration-none text-dark">
              <div class="d-flex flex-column align-items-end border border-1 p-3 bg-info rounded-3 text-white h-100">
                <span class="fs-3 fw-bold">Total Restocks</span>
                <span class="fs-1 fw-bold">
                  <?php
                  $userId = $_SESSION['user_id'];
                  $result = $conn->query("SELECT COUNT(*) AS total FROM Restock WHERE OwnerId = $userId");
                  $row = $result->fetch_assoc();
                  echo $row['total'];
                  ?>
                </span>
              </div>
            </a>
          </div>
          <div class="col-xl-3 col-6">
            <a href="stockout.php" class="text-decoration-none text-white">
              <div class="d-flex flex-column align-items-end border border-1 p-3 bg-warning rounded-3 h-100">
                <span class="fs-3 fw-bold">Total Stock Outs</span>
                <span class="fs-1 fw-bold">
                  <?php
                  $userId = $_SESSION['user_id'];
                  $result = $conn->query("SELECT COUNT(*) AS total FROM StockOut WHERE OwnerId = $userId");
                  $row = $result->fetch_assoc();
                  echo $row['total'];
                  ?>
                </span>
              </div>
            </a>
          </div>
          <div class="col-xl-3 col-6">
            <a href="#" class="text-decoration-none text-white" onclick="window.scrollTo({top: document.querySelector('.border.border-1.border-opacity-25.border-dark.rounded.p-2').offsetTop - 30, behavior: 'smooth'}); return false;">
              <div class="d-flex flex-column align-items-end border border-1 p-3 bg-danger rounded-3 h-100">
                <span class="fs-3 fw-bold">Total Warnings</span>
                <span class="fs-1 fw-bold">
                  <?php
                  $userId = $_SESSION['user_id'];
                  // Out of stock warnings for this user
                  $result1 = $conn->query("SELECT COUNT(*) AS total FROM Product WHERE CurrentStockNumber < OutOfStockWarningThreshold AND Archived = FALSE AND OwnerId = $userId");
                  $row1 = $result1->fetch_assoc();
                  // Expired/near expired warnings for this user
                  $today = date('Y-m-d');
                  $result2 = $conn->query("SELECT COUNT(*) AS total FROM RestockDetail rd INNER JOIN Product p ON rd.ProductId = p.Id WHERE rd.ExpirationDate IS NOT NULL AND rd.IsExpiredChecked = FALSE AND p.Archived = FALSE AND p.OwnerId = $userId AND rd.ExpirationDate <= DATE_ADD('$today', INTERVAL p.ExpirationWarningThreshold DAY) AND rd.ExpirationDate >= '$today'");
                  $row2 = $result2->fetch_assoc();
                  echo ($row1['total'] + $row2['total']);
                  ?>
                </span>
              </div>
            </a>
          </div>
        </div>
        <div
          class="border border-1 border-opacity-25 rounded p-2 mb-4">
          <span class="fs-4 fw-semibold">Out of Stock Warning</span>
          <div class="table-responsive mt-4" style="max-height: 400px">
            <table
              class="table table-striped table-bordered align-middle">
              <thead>
                <tr>
                  <th scope="col" class="d-none d-md-block">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Stock</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $userId = $_SESSION['user_id'];
                $sql = "SELECT Name, QuantityPerUnit, Unit, Price, CurrentStockNumber FROM Product WHERE CurrentStockNumber < OutOfStockWarningThreshold AND Archived = FALSE AND OwnerId = $userId";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        $name = $row['Name'] . ', ' . $row['QuantityPerUnit'] . ' ' . $row['Unit'] . ', ' . number_format($row['Price'], 2);
                        echo '<tr>';
                        echo '<th scope="row" class="d-none d-md-block">' . $i . '</th>';
                        echo '<td>' . htmlspecialchars($name) . '</td>';
                        echo '<td>' . $row['CurrentStockNumber'] . '</td>';
                        echo '</tr>';
                        $i++;
                    }
                } else {
                    echo '<tr><td colspan="3" class="text-center">No products are below the out of stock threshold.</td></tr>';
                }
              ?>
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end">
            <a class="btn btn-primary" href="restock.php">Restock Products</a>
          </div>
        </div>
        <div class="border border-1 border-opacity-25 rounded p-2">
          <span class="fs-4 fw-semibold">Expired Stocks Warning</span>
          <div class="table-responsive mt-4" style="max-height: 350px">
            <table
              class="table table-striped table-bordered align-middle"
              id="expired-restock-table">
              <thead>
                <tr>
                  <th scope="col" class="d-none d-md-block">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Expiration Date</th>
                  <th scope="col" >Restock Count</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $userId = $_SESSION['user_id'];
                $today = date('Y-m-d');
                $sql = "SELECT rd.Id, p.Name, p.QuantityPerUnit, p.Unit, p.Price, rd.ExpirationDate, rd.Count
                        FROM RestockDetail rd
                        INNER JOIN Product p ON rd.ProductId = p.Id
                        WHERE rd.ExpirationDate IS NOT NULL AND rd.ExpirationDate != ''
                          AND rd.IsExpiredChecked = FALSE
                          AND p.Archived = FALSE
                          AND p.OwnerId = $userId
                          AND rd.ExpirationDate <= DATE_ADD('$today', INTERVAL p.ExpirationWarningThreshold DAY)
                          AND rd.ExpirationDate >= '$today' 
                        ORDER BY rd.ExpirationDate ASC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        $restockDetailId = $row['Id']; // Store the restock product Id as an attribute
                        $name = $row['Name'] . ', ' . $row['QuantityPerUnit'] . ' ' . $row['Unit'] . ', ' . number_format($row['Price'], 2);
                        $expiration = date('F j, Y', strtotime($row['ExpirationDate']));
                        echo '<tr data-restock-detail-id="' . $restockDetailId . '">';
                        echo '<th scope="row" class="d-none d-md-block">' . $i . '</th>';
                        echo '<td>' . htmlspecialchars($name) . '</td>';
                        echo '<td>' . htmlspecialchars($expiration) . '</td>';
                        echo '<td>' . $row['Count'] . '</td>';
                        // You can now use $restockDetailId as needed
                        echo '</tr>';
                        $i++;
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No stocks are near expiration.</td></tr>';
                }
              ?>
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-1" id="check-expired-button">Checked</button>
            <button class="btn btn-secondary">Print Checklist</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      window.activeSidebar = 'dashboard';
      window.sidebarUsername = "<?= htmlspecialchars($_SESSION['username'] ?? '') ?>";
    </script>
    <script src="js/sidebar.js"></script>
    <script src="js/dashboard.js"></script>
  </body>
</html>