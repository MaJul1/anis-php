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
    <title>Stockout</title>
    <link href="bootsrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="bootsrap/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="image/png" href="assets/logo.png">
</head>
<body>
    <!-- modal-create-stock-out -->
    <div class="modal fade" id="create-stock-out" data-bs-backdrop="static">
      <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Create New Stock Out</span>
          </div>
          <div class="modal-body">
            <form id="create-stock-out-form" autocomplete="off">
              <table class="table table-striped table-bordered mb-0" style="min-width: 600px;">
                <thead>
                  <tr>
                    <th>Product Name</th>
                    <th>Stock Out Count</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="stockout-product-rows">
                  <!-- Dynamic rows inserted by JS -->
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-center">
                      <button type="button" class="btn btn-primary" id="add-stockout-row" aria-label="Add new product row">Add New Product</button>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </form>
          </div>
          <div class="modal-footer">
            <button type="submit" form="create-stock-out-form" class="btn btn-primary" id="submit-stockout-btn">Create</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-view-stock-out -->
    <div class="modal fade" id="restock-view">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold" id="stockout-modal-title">Stock Out Details</span>
          </div>
          <div class="modal-body">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Product Stock Count</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="stockout-details-tbody">
                <!-- Dynamic content will be loaded here -->
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary">Print</button>
            <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-update-stock-out -->
    <div class="modal fade" id="update-stock-out" data-bs-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Update Stock Out</span>
          </div>
          <div class="modal-body">
            <form action>
              <div class="col-md mb-3">
                <label for="new-product-name">Product Name</label>
                <input class="form-control" id="new-product-name" type="text"
                  value="Sample Product 1">
              </div>
              <div class="col-md mb-3">
                <label for="new-expiration-date">Product Stock Count</label>
                <input class="form-control" id="new-expiration-date"
                  type="number" value="20">
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

    <!-- modal-add-missing-product -->
    <div class="modal fade" id="add-missing-stock-out" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Add Missing Product</span>
          </div>
          <div class="modal-body">
            <form action="">
              <div class="mb-3">
                <label for="add-missing-stock-out-select">Product</label>
                <select class="form-control" name="missing-stock-out" id="add-missing-stock-out-select"></select>
              </div>
              <div class="col-sm mb-3">
                <label for="add-missing-stock-out-count">Stock Out Count</label>
                <input class="form-control" type="number" id="add-missing-stock-out-count">
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
    <div class="d-flex ps-3 d-md-none pt-1 border-bottom" style="height: 50px; background-color: var(--bs-secondary-bg);">
      <a data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded fs-2 ps-2 pe-2 link-body-emphasis"></i>
      </a>
    </div>

    <div class="d-flex">
      <div id="sidebar"></div>
      <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column">
      <span class="fs-1 fw-bold">Stock Out</span>
      <div class=" mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal"
          data-bs-target="#create-stock-out">Create New Stock Out</button>
      </div>
      <div class="table-responsive" style="max-height: 400px;">
        <table
          class="table table-striped table-hover table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Stock Out Date</th>
              <th>Number of Products</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include_once __DIR__ . '/Persistence/dbconn.php';
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT s.Id, s.CreatedDate, COUNT(sd.Id) as ProductCount
                    FROM stockout s
                    LEFT JOIN stockoutdetail sd ON s.Id = sd.StockOutId
                    WHERE s.OwnerId = ?
                    GROUP BY s.Id, s.CreatedDate
                    ORDER BY s.CreatedDate DESC");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    $stockOutDate = date('F j, Y', strtotime($row['CreatedDate']));
                    echo '<tr data-bs-toggle="modal" data-bs-target="#restock-view" data-stockout-id="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '">';
                    echo '<th>' . $i . '</th>';
                    echo '<td>' . htmlspecialchars($stockOutDate) . '</td>';
                    echo '<td>' . $row['ProductCount'] . '</td>';
                    echo '</tr>';
                    $i++;
                }
            } else {
                echo '<tr><td colspan="3" class="text-center">No stock outs found.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    </div>
    <script>
      window.activeSidebar = 'stockout';
      window.sidebarUsername = "<?= htmlspecialchars($_SESSION['username'] ?? '') ?>";
    </script>
    <script src="js/sidebar.js"></script>
    <!-- Move script to stockout.js -->
    <script src="js/stockout.js"></script>
  </body>
</html>