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
        <input type="date" class="form-control exp-date" aria-label="Expiration Date">
        <div class="form-text">Leave blank if not applicable.</div>
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
        exp.classList.remove('is-invalid');
        expFeedback.textContent = '';
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
  document.querySelectorAll('table.table-striped.table-hover.table-bordered tbody').forEach(tbody => {
    tbody.addEventListener('click', function (e) {
      let row = e.target.closest('tr');
      if (!row) return;
      // Ignore the Add Missing Product row
      if (row.querySelector('button[data-bs-target="#add-missing-product"]')) return;
      // Try to get restock id from data attribute or hidden input
      let restockId = row.getAttribute('data-restock-id');
      if (!restockId) {
        const idInput = row.querySelector('.restock-id');
        restockId = idInput ? idInput.value : null;
      }
      if (!restockId) return;
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
              let expirationDisplay = '';
              if (detail.ExpirationDate === null || detail.ExpirationDate === '' || detail.ExpirationDate === undefined) {
                expirationDisplay = '<span class="text-muted">N/A</span>';
              } else {
                expirationDisplay = new Date(detail.ExpirationDate).toLocaleDateString();
              }
              tr.innerHTML = `
                <td>${detail.ProductName}</td>
                <td>${expirationDisplay}</td>
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
    addMissingProductModal.querySelector('.btn-primary').addEventListener('click', function () {
      const select = document.getElementById('add-missing-product-select');
      const productId = select.value;
      const expirationInput = document.getElementById('add-missing-product-expiration-date');
      const expirationDate = expirationInput ? expirationInput.value : null;
      const countInput = document.getElementById('add-missing-product-count');
      const count = countInput ? countInput.value : null;
      if (!productId) {
        alert('Please select a product.');
        return;
      }
      if (!count || isNaN(count) || parseInt(count) < 1) {
        alert('Please enter a valid count.');
        return;
      }
      // Validate expiration date if provided
      if (expirationDate && (isNaN(Date.parse(expirationDate)) || new Date(expirationDate) < new Date().setHours(0,0,0,0))) {
        alert('Enter a valid future expiration date or leave blank.');
        return;
      }
      const restockId = addMissingProductModal.getAttribute('data-restock-id');
      fetch('Persistence/RestockRepository/addMissingProduct.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          restock_id: restockId,
          product_id: productId,
          expiration_date: expirationDate === '' ? null : expirationDate,
          count: count
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Close the modal and refresh the restock details
            const modal = bootstrap.Modal.getOrCreateInstance(addMissingProductModal);
            modal.hide();
            window.location.reload(); // Reload the page after adding missing product
          } else {
            alert(data.message || 'Failed to add missing product.');
          }
        });
    });
  }

  // --- Print Restock Details Logic ---
  function setupRestockPrintButton() {
    const printBtn = document.getElementById('print-restock-product-table-button');
    if (!printBtn) return;
    printBtn.onclick = function () {
      // Get modal and table
      const modal = document.getElementById('restock-view');
      const table = modal.querySelector('table');
      const header = modal.querySelector('.modal-header span');
      if (!table) return;
      // Remove the last th (Action) from thead for print
      const thead = table.querySelector('thead');
      let theadHtml = '';
      if (thead) {
        const thRow = thead.querySelector('tr');
        if (thRow && thRow.children.length > 3) {
          const thClone = thRow.cloneNode(true);
          thClone.removeChild(thClone.lastElementChild);
          theadHtml = `<tr>${thClone.innerHTML}</tr>`;
        } else if (thRow) {
          theadHtml = `<tr>${thRow.innerHTML}</tr>`;
        }
      }
      // Build HTML for print
      let title = header ? header.textContent : 'Restock Details';
      let html = `
        <html>
        <head>
          <title>${title}</title>
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
            <thead>${theadHtml}</thead>
            <tbody>`;
      // Only print product rows (skip the add-missing-product row and the action column)
      table.querySelectorAll('tbody tr').forEach(row => {
        if (row.querySelector('button[data-bs-target="#add-missing-product"]')) return;
        const clone = row.cloneNode(true);
        if (clone.children.length > 3) {
          clone.removeChild(clone.lastElementChild);
        }
        html += `<tr>${clone.innerHTML}</tr>`;
      });
      html += `</tbody></table></body></html>`;
      // Open print window
      const printWindow = window.open('', '', 'width=800,height=600');
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    };
  }
});
