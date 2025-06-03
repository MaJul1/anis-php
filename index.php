<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  </head>

  <body>

    <!-- sidebar -->
    <div class="d-flex bg-light ps-3 d-md-none pt-1" style="height: 50px;">
      <a data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded text-dark fs-2 ps-2 pe-2"></i>
      </a>
    </div>

    <div class="d-flex">
      <div class="offcanvas-md offcanvas-start p-3 bg-light position-fixed" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 235px; height: 100vh">
      <a class="d-flex align-items-center mb-0 mb-md-1 link-dark text-decoration-none" href="index.php">
        <div class="bg-secondary me-3 rounded" style="width: 40px; height: 40px"></div>
        <span class="fs-4 d-md-inline fw-semibold">ANIS</span>
      </a>
      <hr />
      <div class="mb-1 bg-primary p-2 rounded">
        <a class="text-white text-decoration-none d-flex" href="#">
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
      <div class="mb-1 p-2 rounded">
        <a class="text-dark text-decoration-none d-flex" href="stockout.php">
          <i class="bi bi-box-arrow-up me-2"></i>
          Stock Out
        </a>
      </div>
    </div>
    <div class="d-none d-md-block" style="min-width: 235px; height: 100vh;"></div>
    
    <!-- body -->
    <div class="container ms-lg-4 p-md-5 pt-3 d-flex flex-column">
      <span class="fs-1 fw-bold mb-3">Dashboard</span>
      <div
        class="border border-1 border-opacity-25 border-dark rounded p-2 mb-4">
        <span class="fs-4 fw-semibold">Out of Stock Warning</span>
        <div class="table-responsive mt-4" style="max-height: 400px">
          <table
            class="table table-striped table-bordered align-middle table-hover table-light">
            <thead class="table-light">
              <tr>
                <th scope="col" class="d-none d-md-block">#</th>
                <th scope="col">Name</th>
                <th scope="col">Stock</th>
              </tr>
            </thead>
            <tbody>
            <?php
              include_once __DIR__ . '/Persistence/dbconn.php';

              $sql = "SELECT Name, QuantityPerUnit, Unit, Price, CurrentStockNumber FROM Product WHERE CurrentStockNumber < OutOfStockWarningThreshold AND Archived = FALSE";
              $result = $conn->query($sql);
              if ($result && $result->num_rows > 0) {
                  $i = 1;
                  while ($row = $result->fetch_assoc()) {
                      $name = $row['Name'] . ', ' . $row['QuantityPerUnit'] . ', ' . $row['Unit'] . ', ' . number_format($row['Price'], 2);
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
          <a class="btn btn-primary" href="restock.html">Restock Products</a>
        </div>
      </div>
      <div class="border border-1 border-opacity-25 border-dark rounded p-2">
        <span class="fs-4 fw-semibold">Expired Stocks Warning</span>
        <div class="table-responsive mt-4" style="max-height: 350px">
          <table
            class="table table-striped table-bordered align-middle table-hover table-light">
            <thead class="table-light">
              <tr>
                <th scope="col" class="d-none d-md-block">#</th>
                <th scope="col">Name</th>
                <th scope="col">Expiration Date</th>
                <th scope="col" >Restock Count</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $today = date('Y-m-d');
              $sql = "SELECT p.Name, p.QuantityPerUnit, p.Unit, p.Price, rd.ExpirationDate, rd.Count
                      FROM RestockDetail rd
                      INNER JOIN Product p ON rd.ProductId = p.Id
                      WHERE rd.ExpirationDate IS NOT NULL
                        AND rd.IsExpiredChecked = FALSE
                        AND p.Archived = FALSE
                        AND rd.ExpirationDate <= DATE_ADD('$today', INTERVAL p.ExpirationWarningThreshold DAY)
                        AND rd.ExpirationDate >= '$today' 
                      ORDER BY rd.ExpirationDate ASC";
              $result = $conn->query($sql);
              if ($result && $result->num_rows > 0) {
                  $i = 1;
                  while ($row = $result->fetch_assoc()) {
                      $name = $row['Name'] . ', ' . $row['QuantityPerUnit'] . ', ' . $row['Unit'] . ', ' . number_format($row['Price'], 2);
                      $expiration = date('F j, Y', strtotime($row['ExpirationDate']));
                      echo '<tr>';
                      echo '<th scope="row" class="d-none d-md-block">' . $i . '</th>';
                      echo '<td>' . htmlspecialchars($name) . '</td>';
                      echo '<td>' . htmlspecialchars($expiration) . '</td>';
                      echo '<td>' . $row['Count'] . '</td>';
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
          <button class="btn btn-primary me-1">Checked</button>
          <button class="btn btn-secondary">Print Checklist</button>
        </div>
      </div>
    </div>
  </body>
</html>