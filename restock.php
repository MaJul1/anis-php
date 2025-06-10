<?php
// Add this at the very top of the file, before <!DOCTYPE html>
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    include_once __DIR__ . '/Persistence/dbconn.php';
    $sql = "SELECT r.Id, r.CreatedDate, COUNT(rd.Id) as ProductCount
            FROM Restock r
            LEFT JOIN RestockDetail rd ON r.Id = rd.RestockId
            GROUP BY r.Id, r.CreatedDate
            ORDER BY r.CreatedDate DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $i = 1;
        while ($row = $result->fetch_assoc()) {
            $restockDate = date('F j, Y', strtotime($row['CreatedDate']));
            echo '<tr data-bs-toggle="modal" data-bs-target="#restock-view">';
            echo '<input type="hidden" class="restock-id" value="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '">';
            echo '<th>' . $i . '</th>';
            echo '<td>' . htmlspecialchars($restockDate) . '</td>';
            echo '<td>' . $row['ProductCount'] . '</td>';
            echo '</tr>';
            $i++;
        }
    } else {
        echo '<tr><td colspan="3" class="text-center">No restocks found.</td></tr>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
  </head>
  <body>
    <!-- modal-create-restock -->
    <div class="modal fade" id="create-restock" data-bs-backdrop="static" tabindex="-1" aria-labelledby="createRestockLabel" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold" id="createRestockLabel">Create New Restock</span>
          </div>
          <div class="modal-body">
            <form id="create-restock-form" autocomplete="off">
              <table class="table table-striped table-light table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Product Name</th>
                    <th>Expiration Date</th>
                    <th>Restock Count</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="restock-product-rows">
                  <!-- Dynamic rows inserted by JS -->
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="4" class="text-center">
                      <button type="button" class="btn btn-primary" id="add-restock-row" aria-label="Add new product row">Add New Product</button>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </form>
          </div>
          <div class="modal-footer">
            <button type="submit" form="create-restock-form" class="btn btn-primary" id="submit-restock-btn">Create</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-view-restock -->
    <div class="modal fade" id="restock-view">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">May 10, 2025 Restock</span>
          </div>
          <div class="modal-body">
            <input type="hidden" id="modal-restock-id" value="">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Expiration Date</th>
                  <th>Product Stock Count</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4" class="text-center"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-missing-product">Add Missing Product</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary btn-print-restock">Print</button>
            <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-add-missing-product -->
    <div class="modal fade" id="add-missing-product" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Add Missing Product</span>
          </div>
          <div class="modal-body">
            <form action="">
              <div class="mb-3">
                <label for="add-missing-product-select">Product</label>
                <select class="form-control" name="missing-product" id="add-missing-product-select"></select>
              </div>
              <div class="row">
                <div class="col-sm mb-3">
                  <label for="add-missing-product-expiration-date">Expiration Date</label>
                  <input class="form-control" type="date" id="add-missing-product-expiration-date">
                </div>
                <div class="col-sm mb-3">
                  <label class="form-label" for="add-missing-product-count">Stock Count</label>
                  <input class="form-control" type="number" id="add-missing-product-count">
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary">Save</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- sidebar (mobile toggle) -->
    <div class="d-flex bg-light ps-3 d-md-none pt-1" style="height: 50px;">
      <a data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded text-dark fs-2 ps-2 pe-2"></i>
      </a>
    </div>

    <div class="d-flex">
      <div id="sidebar"></div>
      <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column">
        <span class="fs-1 fw-bold">Restocks</span>
        <div class=" mb-3">
          <button class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#create-restock">Create New Restock</button>
        </div>
        <div class="table-responsive" style="max-height: 400px;">
          <table
            class="table table-striped table-light table-hover table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Restock Date</th>
                <th>Number of Products</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include_once __DIR__ . '/Persistence/dbconn.php';
              // Fetch all restocks with their id, date, and number of products restocked
              $sql = "SELECT r.Id, r.CreatedDate, COUNT(rd.Id) as ProductCount
                      FROM Restock r
                      LEFT JOIN RestockDetail rd ON r.Id = rd.RestockId
                      GROUP BY r.Id, r.CreatedDate
                      ORDER BY r.CreatedDate DESC";
              $result = $conn->query($sql);
              if ($result && $result->num_rows > 0) {
                  $i = 1;
                  while ($row = $result->fetch_assoc()) {
                      $restockDate = date('F j, Y', strtotime($row['CreatedDate']));
                      echo '<tr data-bs-toggle="modal" data-bs-target="#restock-view" data-restock-id="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '">';
                      echo '<input type="hidden" class="restock-id" value="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '">';
                      echo '<th>' . $i . '</th>';
                      echo '<td>' . htmlspecialchars($restockDate) . '</td>';
                      echo '<td>' . $row['ProductCount'] . '</td>';
                      echo '</tr>';
                      $i++;
                  }
              } else {
                  echo '<tr><td colspan="3" class="text-center">No restocks found.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
      <script>window.activeSidebar = 'restock';</script>
      <script src="js/sidebar.js"></script>
      <script src="js/restock.js"></script>
  </body>
</html>