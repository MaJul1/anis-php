<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</head>
<body>
    <!-- modal-create-restock -->
    <div class="modal fade" id="create-stock-out" data-bs-backdrop="static">
      <div class="modal-dialog modal-xl modal-dialog- modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">Create New Stock Out</span>
          </div>
          <div class="modal-body">
            <table class="table table-striped table-light table-bordered">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Stock Out Count</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select id="product-select-1" class="form-select"
                      aria-label="Default select example"></select>
                  </td>
                  <td><input type="number" value="20"></td>
                  <td class="text-center"><button
                      class="btn btn-danger">Delete</button></td>
                </tr>
                <tr>
                  <td>
                    <select id="product-select-2" class="form-select"
                      aria-label="Default select example"></select>
                  </td>
                  <td><input type="number" value="20"></td>
                  <td class="text-center"><button
                      class="btn btn-danger">Delete</button></td>
                </tr>
                <tr>
                  <td>
                    <select id="product-select-3" class="form-select"
                      aria-label="Default select example"></select>
                  </td>
                  <td><input type="number" value="20"></td>
                  <td class="text-center"><button
                      class="btn btn-danger">Delete</button></td>
                </tr>
                <tr>
                  <th colspan="4" class="text-center"><button
                      class="btn btn-primary">Add New Product</button></th>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary">Create</button>
            <button class="btn btn-secondary"
              data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal-view-restock -->
    <div class="modal fade" id="restock-view">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <span class="fs-3 fw-semibold">May 10, 2025 Stock Out</span>
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
              <tbody>
                <tr>
                  <td>Sample Product 1</td>
                  <td>20</td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#update-stock-out">Edit</button>
                    <button class="btn btn-danger btn-sm"> Delete</button>
                  </td>
                </tr>
                 <tr>
                  <td>Sample Product 1</td>
                  <td>20</td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#update-stock-out">Edit</button>
                    <button class="btn btn-danger btn-sm"> Delete</button>
                  </td>
                </tr>
                 <tr>
                  <td>Sample Product 1</td>
                  <td>20</td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#update-stock-out">Edit</button>
                    <button class="btn btn-danger btn-sm"> Delete</button>
                  </td>
                </tr>
                 <tr>
                  <td>Sample Product 1</td>
                  <td>20</td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#update-stock-out">Edit</button>
                    <button class="btn btn-danger btn-sm"> Delete</button>
                  </td>
                </tr>
                <tr>
                  <td colspan="4" class="text-center"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-missing-stock-out">Add Missing Product</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                <label for="add-missing-stock-out-count">Stock Count</label>
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

    <div class="d-flex bg-light ps-3 d-md-none pt-1" style="height: 50px;">
      <a data-bs-toggle="offcanvas" href="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list border border-1 border-secondary rounded text-dark fs-2 ps-2 pe-2"></i>
      </a>
    </div>

    <!-- body -->
    <div class="d-flex">
      <!-- sidebar -->
      <div class="offcanvas-md offcanvas-start p-3 bg-light position-fixed" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 235px; height: 100vh">
        <a class="d-flex align-items-center mb-0 mb-md-1 link-dark text-decoration-none" href="index.html">
          <div class="bg-secondary me-3 rounded" style="width: 40px; height: 40px"></div>
          <span class="fs-4 d-md-inline fw-semibold">ANIS</span>
        </a>
        <hr />
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="index.html">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
          </a>
        </div>
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="product.html">
              <i class="bi bi-box me-2"></i>
              Products
          </a>
        </div>
        <div class="mb-1 p-2 rounded">
          <a class="text-dark text-decoration-none d-flex" href="restock.html">
              <i class="bi bi-box-arrow-in-down me-2"></i>
              Restock
          </a>
        </div>
        <div class="mb-1 bg-primary p-2 rounded">
          <a class="text-white text-decoration-none d-flex" href="#">
            <i class="bi bi-box-arrow-up me-2"></i>
            Stock Out
          </a>
        </div>
    </div>
    <div class="d-none d-md-block" style="min-width: 235px; height: 100vh;"></div>
      
      <div class="container ms-md-4 p-md-5 pt-3 d-flex flex-column">
      <span class="fs-1 fw-bold">Stock Out</span>
      <div class=" mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal"
          data-bs-target="#create-stock-out">Create New Stock Out</button>
      </div>
      <div class="table-responsive" style="max-height: 400px;">
        <table
          class="table table-striped table-light table-hover table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Stock Out Date</th>
              <th>Number of Products</th>
            </tr>
          </thead>
          <tbody>
            <tr data-bs-toggle="modal" data-bs-target="#restock-view">
              <th>1</th>
              <td>May 6, 2025</td>
              <td>6</td>
            </tr>
            <tr data-bs-toggle="modal" data-bs-target="#restock-view">
              <th>2</th>
              <td>May 9, 2025</td>
              <td>7</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div>

    <script>
      const products = [
          { value: "", text: "Select Product" },
          { value: "1", text: "Sample Product 1" },
          { value: "2", text: "Sample Product 2" },
          { value: "3", text: "Sample Product 3" }
        ];

        function populateSelect(selectId) {
          const select = document.getElementById(selectId);
          products.forEach(opt => {
            const option = document.createElement("option");
            option.value = opt.value;
            option.textContent = opt.text;
            select.appendChild(option);
          });
        }

        populateSelect("product-select-1");
        populateSelect("product-select-2");
        populateSelect("product-select-3");
        populateSelect("add-missing-stock-out-select");
      </script>
</body>
</html>