document.addEventListener('DOMContentLoaded', function () {
  const rows = document.querySelectorAll('#product-table tbody tr');
  rows.forEach(row => {
    row.addEventListener('click', function () {
      // Get values from data attributes
      const name = this.getAttribute('data-name') || '';
      const price = this.getAttribute('data-price') || '';
      const unit = this.getAttribute('data-unit') || '';
      const qpu = this.getAttribute('data-qpu') || '';
      const expWarn = this.getAttribute('data-expwarn') || '';
      const stockWarn = this.getAttribute('data-stockwarn') || '';
      // Set modal values
      document.querySelector('#read-product .modal-header').textContent = name;
      document.getElementById('read-product-price').value = price;
      document.getElementById('read-product-unit').value = unit;
      document.getElementById('read-product-qpu').value = qpu;
      document.getElementById('read-product-days-until-expiration-warning').value = expWarn;
      document.getElementById('read-product-stock-warning-threshold').value = stockWarn;
    });
  });
});

const datalist = document.getElementById("product-suggestions");

productNames.forEach(name => {
    const option = document.createElement("option");
    option.value = name;
    datalist.appendChild(option);
});

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.querySelector('input[placeholder="Product Name"]');
  const searchButton = document.getElementById("button-search");
  const table = document.getElementById("product-table");
  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));

  // Create the "no product found" row
  const noProductRow = document.createElement("tr");
  const noProductCell = document.createElement("td");
  noProductCell.colSpan = 4;
  noProductCell.className = "text-center text-secondary";
  noProductCell.textContent = "No product found";
  noProductRow.appendChild(noProductCell);

  function filterTable() {
    const searchValue = searchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    rows.forEach(row => {
      const nameCell = row.querySelectorAll("td")[0];
      if (!nameCell) return;
      const name = nameCell.textContent.trim().toLowerCase();
      const match = name.includes(searchValue);
      row.style.display = match ? "" : "none";
      if (match) visibleCount++;
    });

    // Remove existing "no product found" row if present
    if (tbody.contains(noProductRow)) {
      tbody.removeChild(noProductRow);
    }

    // If no rows are visible, show the "no product found" row
    if (visibleCount === 0) {
      tbody.appendChild(noProductRow);
    }
  }

  // Filter when search button is clicked
  searchButton.addEventListener("click", filterTable);
});

document.addEventListener('DOMContentLoaded', function () {
  // Archive button logic
  const archiveBtn = document.querySelector('#read-product .btn.btn-primary:not([data-bs-toggle])');
  let selectedProductId = '';
  let selectedProductName = '';
  // Set selected product id and name on row click
  const rows = document.querySelectorAll('#product-table tbody tr');
  rows.forEach(row => {
    row.addEventListener('click', function () {
      selectedProductId = this.getAttribute('data-id') || '';
      selectedProductName = this.getAttribute('data-name') || '';
    });
  });
  if (archiveBtn) {
    archiveBtn.addEventListener('click', function () {
      if (!selectedProductId) return;
      if (confirm('Are you sure you want to archive this product?')) {
        fetch('Persistence/ProductRepository/archiveProduct.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + encodeURIComponent(selectedProductId)
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            alert('Product archived successfully!');
            location.reload();
          } else {
            alert('Failed to archive product.');
          }
        });
      }
    });
  }

  // Unarchive button logic for archived products
  document.querySelectorAll('#archived-product-table .btn.btn-primary').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = this.closest('tr');
      const productId = row.getAttribute('data-id');
      if (!productId) return;
      if (confirm('Are you sure you want to unarchive this product?')) {
        fetch('Persistence/ProductRepository/unarchiveProduct.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + encodeURIComponent(productId)
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            alert('Product unarchived successfully!');
            location.reload();
          } else {
            alert('Failed to unarchive product.');
          }
        });
      }
    });
  });
});
