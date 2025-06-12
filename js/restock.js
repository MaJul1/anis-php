// js/restock.js
// Handles dynamic rows, product dropdowns, validation, AJAX, and UI feedback for Create New Restock modal

document.addEventListener('DOMContentLoaded', function () {
  const restockRows = document.getElementById('restock-product-rows');
  const addRowBtn = document.getElementById('add-restock-row');
  const form = document.getElementById('create-restock-form');
  const submitBtn = document.getElementById('submit-restock-btn');
  let productOptions = [];

  // Fetch products for dropdown
  fetch('Persistence/ProductRepository/getNonArchivedProducts.php')
    .then(res => res.json())
    .then(data => {
      productOptions = data.products || [];
      addRestockRow(); // Add initial row
    });

  // Add new row
  function addRestockRow() {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>
        <select class="form-select product-select" required aria-label="Product Name">
          <option value="">Select product</option>
          ${productOptions.map(p => `<option value="${p.ProductID}">${p.ProductName}</option>`).join('')}
        </select>
        <div class="invalid-feedback"></div>
      </td>
      <td>
        <input type="date" class="form-control exp-date" required aria-label="Expiration Date">
        <div class="invalid-feedback"></div>
      </td>
      <td>
        <input type="number" class="form-control restock-count" min="1" required aria-label="Restock Count">
        <div class="invalid-feedback"></div>
      </td>
      <td>
        <button type="button" class="btn btn-danger remove-row-btn" aria-label="Remove row">&times;</button>
      </td>
    `;
    restockRows.appendChild(row);
    row.querySelector('.remove-row-btn').onclick = () => {
      row.remove();
      if (restockRows.children.length === 0) addRestockRow();
    };
  }

  addRowBtn.addEventListener('click', addRestockRow);

  // Form validation
  function validateForm() {
    let valid = true;
    Array.from(restockRows.children).forEach(row => {
      // Product
      const product = row.querySelector('.product-select');
      const productFeedback = product.nextElementSibling;
      if (!product.value) {
        product.classList.add('is-invalid');
        productFeedback.textContent = 'Please select a product.';
        valid = false;
      } else {
        product.classList.remove('is-invalid');
        productFeedback.textContent = '';
      }
      // Expiration Date
      const exp = row.querySelector('.exp-date');
      const expFeedback = exp.nextElementSibling;
      if (!exp.value) {
        exp.classList.add('is-invalid');
        expFeedback.textContent = 'Expiration date required.';
        valid = false;
      } else if (isNaN(Date.parse(exp.value)) || new Date(exp.value) < new Date().setHours(0,0,0,0)) {
        exp.classList.add('is-invalid');
        expFeedback.textContent = 'Enter a valid future date.';
        valid = false;
      } else {
        exp.classList.remove('is-invalid');
        expFeedback.textContent = '';
      }
      // Count
      const count = row.querySelector('.restock-count');
      const countFeedback = count.nextElementSibling;
      if (!count.value || isNaN(count.value) || parseInt(count.value) < 1) {
        count.classList.add('is-invalid');
        countFeedback.textContent = 'Enter a positive number.';
        valid = false;
      } else {
        count.classList.remove('is-invalid');
        countFeedback.textContent = '';
      }
    });
    return valid;
  }

  // Submit form
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validateForm()) return;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';
    const restockData = Array.from(restockRows.children).map(row => ({
      product_id: row.querySelector('.product-select').value,
      expiration_date: row.querySelector('.exp-date').value,
      count: row.querySelector('.restock-count').value
    }));
    fetch('Persistence/RestockRepository/createRestock.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ products: restockData })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Success: reset form, close modal, refresh restock table
          form.reset();
          restockRows.innerHTML = '';
          addRestockRow();
          const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('create-restock'));
          modal.hide();
          window.location.reload(); // Reload the page to update the table
        } else {
          // Show error
          alert(data.message || 'Failed to create restock.');
        }
      })
      .catch(() => alert('Server error.'))
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create';
      });
  });

  // Accessibility: allow Enter to submit, Esc to close
  form.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
      e.preventDefault();
      submitBtn.click();
    }
  });

  // Refresh restock table via AJAX
  function refreshRestockTable() {
    const tableBody = document.querySelector('table.table-striped.table-light.table-hover.table-bordered tbody');
    if (!tableBody) return;
    fetch(window.location.pathname, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(res => res.text())
      .then(html => {
        // Extract the new tbody from the HTML response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTbody = doc.querySelector('table.table-striped.table-light.table-hover.table-bordered tbody');
        if (newTbody) tableBody.innerHTML = newTbody.innerHTML;
      });
  }

  // Delegate click event for restock table rows
  document.querySelectorAll('table.table-striped.table-light.table-hover.table-bordered tbody').forEach(tbody => {
    tbody.addEventListener('click', function (e) {
      let row = e.target.closest('tr');
      if (!row) return;
      const idInput = row.querySelector('.restock-id');
      if (!idInput) return;
      const restockId = idInput.value;
      // Set the restock id in the hidden input in the modal
      const modalRestockIdInput = document.getElementById('modal-restock-id');
      if (modalRestockIdInput) modalRestockIdInput.value = restockId;
      fetchRestockDetails(restockId);
    });
  });

  // --- Update Restock Modal Logic ---
  let lastRestockDetails = [];
  let lastRestockProducts = [];
  let lastRestockId = null;

  // When showing restock details, store them for edit
  function fetchRestockDetails(restockId) {
    fetch('Persistence/RestockRepository/getRestockDetails.php?id=' + encodeURIComponent(restockId))
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;
        lastRestockId = restockId;
        lastRestockDetails = data.details;
        // Get product list for select
        fetch('Persistence/ProductRepository/getNonArchivedProducts.php')
          .then(res => res.json())
          .then(prodData => {
            lastRestockProducts = prodData.products || [];
            // Update modal header with date
            const modal = document.getElementById('restock-view');
            const header = modal.querySelector('.modal-header span');
            const date = new Date(data.createdDate);
            header.textContent = date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) + ' Restock';
            // Update table body
            const tbody = modal.querySelector('tbody');
            tbody.innerHTML = '';
            data.details.forEach((detail, idx) => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${detail.ProductName}</td>
                <td>${detail.ExpirationDate ? new Date(detail.ExpirationDate).toLocaleDateString() : ''}</td>
                <td>${detail.Count}</td>
                <td class="text-center">
                  <button class="btn btn-danger btn-sm" data-delete-idx="${idx}">Delete</button>
                </td>
              `;
              tbody.appendChild(tr);
            });
            // Add the "Add Missing Product" row
            const addRow = document.createElement('tr');
            addRow.innerHTML = `<td colspan="4" class="text-center"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-missing-product">Add Missing Product</button></td>`;
            tbody.appendChild(addRow);
            // Add event listener for delete buttons after rendering
            tbody.querySelectorAll('[data-delete-idx]').forEach(btn => {
              btn.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent row click
                const idx = parseInt(this.getAttribute('data-delete-idx'));
                const detail = lastRestockDetails[idx];
                if (!detail) return;
                if (!confirm('Are you sure you want to delete this restock product?')) return;
                fetch('Persistence/RestockRepository/deleteRestockProduct.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ restock_detail_id: detail.RestockDetailId || detail.Id })
                })
                  .then(res => res.json())
                  .then(data => {
                    if (data.success) {
                      if (data.deletedRestock) {
                        // If the whole restock was deleted, close the modal and refresh the table
                        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('restock-view'));
                        modal.hide();
                        window.location.reload(); // Reload the page to update the table
                      } else {
                        // Otherwise, just refresh the modal content
                        if (lastRestockId) fetchRestockDetails(lastRestockId);
                      }
                    } else {
                      alert(data.message || 'Failed to delete restock product.');
                    }
                  });
              });
            });
            // After updating the table, re-attach the print button logic
            setupRestockPrintButton();
          });
      });
  }

  // --- Add Missing Product Modal Logic ---
  const addMissingProductModal = document.getElementById('add-missing-product');
  if (addMissingProductModal) {
    addMissingProductModal.addEventListener('show.bs.modal', function () {
      const select = document.getElementById('add-missing-product-select');
      select.innerHTML = '<option value="">Select product</option>';
      fetch('Persistence/ProductRepository/getNonArchivedProducts.php')
        .then(res => res.json())
        .then(data => {
          (data.products || []).forEach(p => {
            const option = document.createElement('option');
            option.value = p.ProductID;
            option.textContent = p.ProductName;
            select.appendChild(option);
          });
        });
      // Set the restock id for the add-missing-product modal
      const modalRestockIdInput = document.getElementById('modal-restock-id');
      addMissingProductModal.setAttribute('data-restock-id', modalRestockIdInput ? modalRestockIdInput.value : '');
    });
    // Handle Save button click
    const addMissingProductForm = document.getElementById('add-missing-product-form');
    addMissingProductForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const productId = document.getElementById('add-missing-product-select').value;
      if (!productId) {
        alert('Select a product to add.');
        return;
      }
      const restockId = addMissingProductModal.getAttribute('data-restock-id');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Adding...';
      fetch('Persistence/RestockRepository/addMissingProduct.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ restock_id: restockId, product_id: productId })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Close modal and refresh restock details
            const modal = bootstrap.Modal.getOrCreateInstance(addMissingProductModal);
            modal.hide();
            if (lastRestockId) fetchRestockDetails(lastRestockId);
          } else {
            alert(data.message || 'Failed to add product.');
          }
        })
        .catch(() => alert('Server error.'))
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Save';
        });
    });
  }

  // --- Print Restock Details Logic ---
  function setupRestockPrintButton() {
    const modal = document.getElementById('restock-view');
    if (!modal) return;
    const table = modal.querySelector('table');
    if (!table) return;
    const printBtn = modal.querySelector('.btn-print-restock');
    if (!printBtn || !printBtn.parentNode) return;
    const newPrintBtn = printBtn.cloneNode(true);
    printBtn.parentNode.replaceChild(newPrintBtn, printBtn);
    newPrintBtn.addEventListener('click', function() {
      const title = modal.querySelector('.fs-3.fw-semibold')?.textContent || 'Restock Details';
      const rows = Array.from(table.querySelectorAll('tbody tr'));
      // Only print rows with at least 3 columns and skip the Add Missing Product row
      const printableRows = rows.filter(row => row.querySelectorAll('td').length >= 3);
      if (printableRows.length === 0) {
        alert('No restock details to print.');
        return;
      }
      let html = `
        <html>
        <head>
          <title>Restock Details</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #333; padding: 4px; text-align: left; }
            th { background: #eee; }
          </style>
        </head>
        <body>
          <h2>${title}</h2>
          <table>
            <thead>
              <tr><th>Product Name</th><th>Expiration Date</th><th>Product Stock Count</th></tr>
            </thead>
            <tbody>
      `;
      printableRows.forEach(row => {
        const tds = row.querySelectorAll('td');
        const name = tds[0]?.textContent.trim() || '';
        const expiration = tds[1]?.textContent.trim() || '';
        const count = tds[2]?.textContent.trim() || '';
        html += `<tr><td>${name}</td><td>${expiration}</td><td>${count}</td></tr>`;
      });
      html += `</tbody></table></body></html>`;
      const printWindow = window.open('', '', 'width=800,height=600');
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    });
  }

  // Attach print logic every time the modal is shown
  (function() {
    document.addEventListener('shown.bs.modal', function(e) {
      if (e.target && e.target.id === 'restock-view') {
        setupRestockPrintButton();
      }
    });
  })();

  // Initial setup: add row and fetch restock details if editing
  addRestockRow();
  const initialRestockId = document.getElementById('modal-restock-id')?.value;
  if (initialRestockId) {
    fetchRestockDetails(initialRestockId);
  }
});
