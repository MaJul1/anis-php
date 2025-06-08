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

  // Delete button logic for archived products
  document.querySelectorAll('#archived-product-table .btn.btn-danger[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = this.closest('tr');
      const productId = row.getAttribute('data-id');
      if (!productId) return;
      if (confirm('Are you sure you want to permanently delete this product? This action cannot be undone.')) {
        fetch('Persistence/ProductRepository/deleteProduct.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + encodeURIComponent(productId)
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            alert('Product deleted successfully!');
            location.reload();
          } else {
            alert('Failed to delete product.');
          }
        });
      }
    });
  });

  // Update modal population
  const editBtn = document.querySelector('#read-product .btn.btn-primary[data-bs-target="#update-product"]');
  editBtn && editBtn.addEventListener('click', function () {
    // Get values from the view modal
    const name = document.querySelector('#read-product .modal-header').textContent.trim();
    const price = document.getElementById('read-product-price').value;
    const unit = document.getElementById('read-product-unit').value;
    const qpu = document.getElementById('read-product-qpu').value;
    const expWarn = document.getElementById('read-product-days-until-expiration-warning').value;
    const stockWarn = document.getElementById('read-product-stock-warning-threshold').value;
    // Also get the selected product id
    const selectedRow = Array.from(document.querySelectorAll('#product-table tbody tr')).find(row => row.getAttribute('data-name') === name);
    const id = selectedRow ? selectedRow.getAttribute('data-id') : '';
    // Set values in update modal
    document.getElementById('udpate-product-name').value = name;
    document.getElementById('udpate-product-price').value = price;
    document.getElementById('udpate-product-unit').value = unit;
    document.getElementById('udpate-product-qpu').value = qpu;
    document.getElementById('udpate-product-days-until-expiration-warning').value = expWarn;
    document.getElementById('udpate-product-stock-warning-threshold').value = stockWarn;
    document.getElementById('update-product-id').value = id;
  });

  // Update product save logic
  const updateSaveBtn = document.getElementById('update-product-save');
  if (updateSaveBtn) {
    updateSaveBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = document.getElementById('update-product-form');
      const formData = new FormData(form);
      fetch('Persistence/ProductRepository/updateProduct.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
      })
      .then(res => res.text())
      .then(data => {
        if (data.trim() === 'success') {
          alert('Product updated successfully!');
          location.reload();
        } else {
          alert('Failed to update product.\n' + data);
        }
      });
    });
  }

  // Override stock modal: set product id and current stock when opened
  const overrideStockBtn = document.querySelector('#read-product .btn.btn-primary[data-bs-target="#override-stock"]');
  overrideStockBtn && overrideStockBtn.addEventListener('click', function () {
    // Get selected product id, name, and current stock
    const name = document.querySelector('#read-product .modal-header').textContent.trim();
    const selectedRow = Array.from(document.querySelectorAll('#product-table tbody tr')).find(row => row.getAttribute('data-name') === name);
    const id = selectedRow ? selectedRow.getAttribute('data-id') : '';
    const currentStock = selectedRow ? selectedRow.querySelectorAll('td')[3]?.textContent?.trim() : '';
    document.getElementById('override-stock-id').value = id;
    document.getElementById('override-stock-product').value = currentStock;
    // Set the label to include the product name
    const overrideLabel = document.getElementById('override-stock-label');
    if (overrideLabel) {
      overrideLabel.textContent = `Override ${name} Stock`;
    }
  });
  // Save override stock
  const overrideStockSaveBtn = document.getElementById('override-stock-save');
  if (overrideStockSaveBtn) {
    overrideStockSaveBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = document.getElementById('override-stock-form');
      const formData = new FormData(form);
      fetch('Persistence/ProductRepository/overrideStock.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
      })
      .then(res => res.text())
      .then(data => {
        if (data.trim() === 'success') {
          alert('Stock overridden successfully!');
          location.reload();
        } else {
          alert('Failed to override stock.');
        }
      });
    });
  }

  // Simple table sort for #product-table
  (function() {
    const table = document.getElementById('product-table');
    if (!table) return;
    const ths = table.querySelectorAll('thead th');
    let sortCol = null;
    let sortAsc = true;

    ths.forEach((th, idx) => {
      th.style.cursor = 'pointer';
      th.addEventListener('click', function() {
        if (sortCol === idx) {
          sortAsc = !sortAsc;
        } else {
          sortCol = idx;
          sortAsc = true;
        }
        sortTable(idx, sortAsc);
        ths.forEach(t => t.classList.remove('table-primary'));
        th.classList.add('table-primary');
      });
    });

    function sortTable(col, asc) {
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort((a, b) => {
        let aText = a.children[col].textContent.trim();
        let bText = b.children[col].textContent.trim();
        // Try to compare as numbers if possible
        let aNum = parseFloat(aText.replace(/[^\d.\-]/g, ''));
        let bNum = parseFloat(bText.replace(/[^\d.\-]/g, ''));
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return asc ? aNum - bNum : bNum - aNum;
        }
        return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
      });
      rows.forEach(row => tbody.appendChild(row));
    }
  })();
});
