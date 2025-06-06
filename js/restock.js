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
          // Auto-refresh restock table
          refreshRestockTable();
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
});
