<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  </head>
  <body>
    <!-- modal-create-product -->
    <div class="modal fade" id="create-product" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Create Product</span>
          </div>
          <div class="modal-body">
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
              </div>
            <?php elseif (isset($success) && $success): ?>
              <div class="alert alert-success">Product created successfully!</div>
              <script>setTimeout(() => { location.reload(); }, 1000);</script>
            <?php endif; ?>
            <form method="post" action="Persistence/ProductRepository/createProduct.php">
              <input type="hidden" name="action" value="create_product">
              <div class="mb-3">
                <label for="create-product-name" class="label">Product Name</label>
                <input type="text" id="create-product-name" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="create-product-price" class="label">Product Price</label>
                <input type="number" id="create-product-price" name="price" class="form-control" min="0" step="0.01" required>
              </div>
              <div class="row">
                <div class="col-sm mb-3">
                  <label for="create-product-unit" class="label">Product Unit</label>
                  <span class="fs-6 text-secondary">Ex. ml, l, roll, pc</span>
                  <input type="text" id="create-product-unit" name="unit" class="form-control" required>
                </div>
                <div class="col-sm mb-3">
                  <label for="create-product-qpu" class="label">Quantity per Unit</label>
                  <input type="number" id="create-product-qpu" name="qpu" class="form-control" min="0" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="create-product-days-until-expiration-warning" class="label">Expiration Warning Threshold (Days)</label>
                <input type="number" id="create-product-days-until-expiration-warning" name="expWarn" class="form-control" min="0" required>
              </div>
              <div class="mb-3">
                <label for="create-product-stock-warning-threshold" class="label">Stock Warning Threshold</label>
                <input type="number" id="create-product-stock-warning-threshold" name="stockWarn" class="form-control" min="0" required>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Create</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-update-product -->
    <div class="modal fade" id="update-product" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Update Product</span>
          </div>
          <div class="modal-body">
            <form id="update-product-form">
              <input type="hidden" id="update-product-id" name="id">
              <div class="mb-3">
                <label for="udpate-product-name" class="label">Product Name</label>
                <input type="text" id="udpate-product-name" name="name" class="form-control">
              </div>
              <div class="mb-3">
                <label for="udpate-product-price" class="label">Product Price</label>
                <input type="number" id="udpate-product-price" name="price" class="form-control">
              </div>
              <div class="row">
                <div class="col-sm mb-3">
                  <label for="udpate-product-unit" class="label">Product Unit</label>
                  <span class="fs-6 text-secondary">Ex. ml, l, roll, pc</span>
                  <input type="text" id="udpate-product-unit" name="unit" class="form-control">
                </div>
                <div class="col-sm mb-3">
                  <label for="udpate-product-qpu" class="label">Quantity per Unit</label>
                  <input type="number" id="udpate-product-qpu" name="qpu" class="form-control">
                </div>
              </div>
              <div class="mb-3">
                <label for="udpate-product-days-until-expiration-warning" class="label">Expiration Warning Threshold (Days)</label>
                <input type="number" id="udpate-product-days-until-expiration-warning" name="expWarn" class="form-control">
              </div>
              <div class="mb-3">
                <label for="udpate-product-stock-warning-threshold" class="label">Stock Warning Threshold</label>
                <input type="number" id="udpate-product-stock-warning-threshold" name="stockWarn" class="form-control">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" id="update-product-save">Save</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal for view -->
     <div class="modal fade" id="read-product" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header fs-3 fw-semibold">Sample Product</div>
          <div class="modal-body">
            <div class="mb-3">
                <label for="read-product-price" class="label">Product Price</label>
                <input type="number" id="read-product-price" class="form-control" value="100" disabled readonly>
              </div>
              <div class="row">
                <div class="col-sm mb-3">
                  <label for="read-product-unit" class="label">Product Unit</label>
                  <span class="fs-6 text-secondary">Ex. ml, l, roll, pc</span>
                  <input type="text" id="read-product-unit" class="form-control" value="ml" disabled readonly>
                </div>
                <div class="col-sm mb-3">
                  <label for="read-product-qpu" class="label">Quantity per Unit</label>
                  <input type="number" id="read-product-qpu" class="form-control" value="500" disabled readonly>
                </div>
              </div>
              <div class="mb-3">
                <label for="read-product-days-until-expiration-warning" class="label">Expiration Warning Threshold (Days)</label>
                <input type="number" id="read-product-days-until-expiration-warning" class="form-control" value="3" disabled readonly>
              </div>
              <div class="mb-3">
                <label for="read-product-stock-warning-threshold" class="label">Stock Warning Threshold</label>
                <input type="number" id="read-product-stock-warning-threshold" class="form-control" value="2" disabled readonly>
              </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#override-stock">Override Stocks</button>
            <button class="btn btn-primary">Archive</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#update-product">Edit</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal" >Close</button>
          </div>
        </div>
      </div>
     </div>

     <!-- modal-for-override-stock -->
      <div class="modal fade" id="override-stock" data-bs-backdrop="static">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <span class="fs-3 fw-semibold">Override Stock</span>
            </div>
            <div class="modal-body">
              <form id="override-stock-form">
                <label class="form-label" for="override-stock-product" id="override-stock-label">Override Stock</label>
                <input class="form-control" type="number" id="override-stock-product" name="stock" required>
                <input type="hidden" id="override-stock-id" name="id">
              </form>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" id="override-stock-save">Save</button>
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

     <!-- modal-for-archives -->
      <div class="modal fade" id="archived-products">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <span class="fs-3 fw-semibold">Archived Products</span>
            </div>
            <div class="modal-body">
              <table class="table table-striped table-light table-hover table-bordered" id="archived-product-table">
                <thead class="table-light">
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Unit Size</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  include_once __DIR__ . '/Persistence/dbconn.php';
                  $sql = "SELECT Id, Name, Price, QuantityPerUnit, Unit FROM Product WHERE Archived = TRUE ORDER BY Name ASC";
                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0) {
                      $i = 1;
                      while ($row = $result->fetch_assoc()) {
                          $unitSize = $row['QuantityPerUnit'] . ' ' . $row['Unit'];
                          echo '<tr data-id="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '">';
                          echo '<th>' . $i . '</th>';
                          echo '<td>' . htmlspecialchars($row['Name']) . '</td>';
                          echo '<td>' . number_format($row['Price'], 2) . ' Php</td>';
                          echo '<td>' . htmlspecialchars($unitSize) . '</td>';
                          echo '<td class="text-center">';
                          echo '<button class="btn btn-primary me-2">Unarchive</button>';
                          echo '<button class="btn btn-danger" data-action="delete">Delete</button>';
                          echo '</td>';
                          echo '</tr>';
                          $i++;
                      }
                  } else {
                      echo '<tr><td colspan="5" class="text-center">No archived products found.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <div class="mb-1 bg-primary p-2 rounded">
          <a class="text-white text-decoration-none d-flex" href="#">
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
        <hr class="m-0">
        <a href="#" class="d-flex align-items-center text-decoration-none text-dark ps-2 rounded">
          <i class="bi bi-person-circle fs-1 me-3"></i>
          Hello Majul
        </a>
      </div>
      <div class="d-none d-md-block" style="min-width: 235px; height: 100vh;"></div>

     <!-- body -->
    <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column">
      <span class="fs-1 fw-bold">Products</span>
      <div class="d-flex mb-3">
        <div class="input-group justify-content-center">
          <input type="text" class="form-control" placeholder="Product Name" aria-label="Product Name" aria-describedby="button-addon2" list="product-suggestions">
          <datalist id="product-suggestions"></datalist>
          <button class="btn btn-outline-secondary me-2" type="button" id="button-search">Search</button>
        </div>
        <button class="d-none d-lg-block btn btn-primary text-light me-2" data-bs-toggle="modal" data-bs-target="#archived-products" style="min-width: 160px;">Archived Products</button>
        <button class="d-lg-none btn btn-primary text-light me-2"><i class="bi bi-archive"></i></button>
        <button class="d-none d-lg-block btn btn-primary text-light" data-bs-toggle="modal" data-bs-target="#create-product" style="min-width: 125px;">Add Product</button>
        <button class="d-lg-none btn btn-primary text-light" data-bs-toggle="modal" data-bs-target="#create-product">+</button>
      </div>
      <div class="table-responsive" style="max-height: 400px;">
        <table class="table table-striped table-light table-hover table-bordered" id="product-table">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Price</th>
              <th scope="col">Unit Size</th>
              <th sorted="col">Current Stocks</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include_once __DIR__ . '/Persistence/dbconn.php';

            $sql = "SELECT Id, Name, QuantityPerUnit, Unit, Price, CurrentStockNumber, ExpirationWarningThreshold, OutOfStockWarningThreshold FROM Product WHERE Archived = FALSE ORDER BY Name ASC";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    $name = $row['Name'];
                    $unitSize = $row['QuantityPerUnit'] . ' ' . $row['Unit'];
                    echo '<tr data-bs-toggle="modal" data-bs-target="#read-product"';
                    echo ' data-id="' . htmlspecialchars($row['Id'], ENT_QUOTES) . '"';
                    echo ' data-name="' . htmlspecialchars($name, ENT_QUOTES) . '"';
                    echo ' data-price="' . htmlspecialchars($row['Price'], ENT_QUOTES) . '"';
                    echo ' data-unit="' . htmlspecialchars($row['Unit'], ENT_QUOTES) . '"';
                    echo ' data-qpu="' . htmlspecialchars($row['QuantityPerUnit'], ENT_QUOTES) . '"';
                    echo ' data-expwarn="' . htmlspecialchars($row['ExpirationWarningThreshold'], ENT_QUOTES) . '"';
                    echo ' data-stockwarn="' . htmlspecialchars($row['OutOfStockWarningThreshold'], ENT_QUOTES) . '"';
                    echo '>';
                    echo '<th>' . $i . '</th>';
                    echo '<td>' . htmlspecialchars($name) . '</td>';
                    echo '<td>' . number_format($row['Price'], 2) . ' Php</td>';
                    echo '<td>' . htmlspecialchars($unitSize) . '</td>';
                    echo '<td>' . $row['CurrentStockNumber'] . '</td>';
                    echo '</tr>';
                    $i++;
                }
            } else {
                echo '<tr><td colspan="5" class="text-center">No products found.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
  const productNames = [
    <?php
      include_once __DIR__ . '/Persistence/dbconn.php';
      $query = "SELECT Name FROM Product;";
      $result = $conn->query($query);
      if ($result && $result->num_rows > 0) {
        $names = [];
        while($row = $result->fetch_assoc()) {
          $names[] = '"' . addslashes($row['Name']) . '"';
        }
        echo implode(',', $names);
      }
    ?>
  ];
  </script>
  <script src="js/product.js"></script>
  </body>
</html>