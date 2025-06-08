document.addEventListener('DOMContentLoaded', function() {
  // Delegate click event for all table rows with data-stockout-id
  document.querySelectorAll('tr[data-stockout-id]').forEach(function(row) {
    row.addEventListener('click', function() {
      const stockoutId = this.getAttribute('data-stockout-id');
      // Set modal title to the selected date
      const dateCell = this.querySelector('td');
      if (dateCell) {
        document.getElementById('stockout-modal-title').textContent = dateCell.textContent + ' Stock Out';
      }
      // Fetch stock out details
      fetch('Persistence/StockOutRepository/getStockOutDetails.php?stockout_id=' + encodeURIComponent(stockoutId))
        .then(response => response.json())
        .then(data => {
          const tbody = document.getElementById('stockout-details-tbody');
          tbody.innerHTML = '';
          if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${item.Name}</td>
                <td>${item.StockOutCount}</td>
                <td class="text-center">
                  <button class="btn btn-danger btn-sm delete-stockout-product" data-product-id="${item.ProductId}"">Delete</button>
                </td>
              `;
              tbody.appendChild(tr);
            });
            // Add the Add Missing Product button row
            const addRow = document.createElement('tr');
            const addBtn = document.createElement('button');
            addBtn.className = 'btn btn-primary';
            addBtn.textContent = 'Add Missing Product';
            addBtn.setAttribute('type', 'button');
            addBtn.setAttribute('data-bs-toggle', 'modal');
            addBtn.setAttribute('data-bs-target', '#add-missing-stock-out');
            addRow.innerHTML = '<td colspan="3" class="text-center"></td>';
            addRow.querySelector('td').appendChild(addBtn);
            tbody.appendChild(addRow);
          } else {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center">No products found.</td></tr>';
          }
          // After rendering rows, add event listeners for delete buttons
          tbody.querySelectorAll('.delete-stockout-product').forEach(btn => {
            btn.addEventListener('click', function(e) {
              e.stopPropagation();
              const productId = this.getAttribute('data-product-id');
              if (!confirm('Are you sure you want to delete this stock out product?')) return;
              fetch('Persistence/StockOutRepository/deleteStockOutProduct.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  stockout_id: currentStockoutId,
                  product_id: productId
                })
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Refresh the modal
                  document.querySelector('tr[data-stockout-id="' + currentStockoutId + '"]').click();
                  window.location.reload();
                } else {
                  alert(data.error || 'Failed to delete product from stock out');
                }
              });
            });
          });
        });
    });
  });
});

function populateAllProductSelect() {
  fetch('Persistence/StockOutRepository/getAllProducts.php')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('add-missing-stock-out-select');
      select.innerHTML = '';
      if (Array.isArray(data) && data.length > 0) {
        data.forEach(product => {
          const option = document.createElement('option');
          option.value = product.Id;
          option.textContent = product.Name;
          select.appendChild(option);
        });
      } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No products available';
        select.appendChild(option);
      }
    });
}

let currentStockoutId = null;

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('tr[data-stockout-id]').forEach(function(row) {
    row.addEventListener('click', function() {
      currentStockoutId = this.getAttribute('data-stockout-id');
    });
  });

  // Listen for Add Missing Product modal show
  const addMissingModal = document.getElementById('add-missing-stock-out');
  addMissingModal.addEventListener('show.bs.modal', function() {
    populateAllProductSelect();
  });

  // Handle Save button
  document.querySelector('#add-missing-stock-out .btn-primary').addEventListener('click', function(e) {
    e.preventDefault();
    const productId = document.getElementById('add-missing-stock-out-select').value;
    const count = document.getElementById('add-missing-stock-out-count').value;
    if (!productId || !count || !currentStockoutId) return;
    if (!confirm('Are you sure you want to add this product to the stock out?')) return;
    fetch('Persistence/StockOutRepository/addMissingProduct.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        stockout_id: currentStockoutId,
        product_id: productId,
        count: count
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Hide modal and refresh details
        const modal = bootstrap.Modal.getOrCreateInstance(addMissingModal);
        modal.hide();
        document.querySelector('tr[data-stockout-id="' + currentStockoutId + '"]').click();
      } else {
        let msg = data.error || 'Failed to add product';
        if (data.current_stock !== undefined) {
          msg += `\nCurrent stock: ${data.current_stock}`;
        }
        alert(msg);
      }
    });
  });
});

// --- Dynamic Stock Out Modal Logic ---
const stockoutRows = document.getElementById('stockout-product-rows');
const addRowBtn = document.getElementById('add-stockout-row');
const form = document.getElementById('create-stock-out-form');
const submitBtn = document.getElementById('submit-stockout-btn');
let productOptions = [];

// Fetch products for dropdown
fetch('Persistence/StockOutRepository/getAllProducts.php')
  .then(res => res.json())
  .then(data => {
    productOptions = data || [];
    addStockOutRow(); // Add initial row
  });

// Add new row
function addStockOutRow() {
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>
      <select class="form-select product-select" required aria-label="Product Name">
        <option value="">Select product</option>
        ${productOptions.map(p => `<option value="${p.Id}">${p.Name}</option>`).join('')}
      </select>
      <div class="invalid-feedback"></div>
    </td>
    <td>
      <input type="number" class="form-control stockout-count" min="1" required aria-label="Stock Out Count">
      <div class="invalid-feedback"></div>
    </td>
    <td>
      <button type="button" class="btn btn-danger remove-row-btn" aria-label="Remove row">&times;</button>
    </td>
  `;
  stockoutRows.appendChild(row);
  row.querySelector('.remove-row-btn').onclick = () => {
    row.remove();
    if (stockoutRows.children.length === 0) addStockOutRow();
  };
}

addRowBtn.addEventListener('click', addStockOutRow);

// Form validation
function validateStockOutForm() {
  let valid = true;
  Array.from(stockoutRows.children).forEach(row => {
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
    // Count
    const count = row.querySelector('.stockout-count');
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

form.addEventListener('submit', function (e) {
  e.preventDefault();
  if (!validateStockOutForm()) return;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Creating...';
  const stockoutData = Array.from(stockoutRows.children).map(row => ({
    product_id: row.querySelector('.product-select').value,
    count: row.querySelector('.stockout-count').value
  }));
  fetch('Persistence/StockOutRepository/createStockOut.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ products: stockoutData })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Success: reset form, close modal, refresh stockout table
        form.reset();
        stockoutRows.innerHTML = '';
        addStockOutRow();
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('create-stock-out'));
        modal.hide();
        window.location.reload();
      } else {
        alert(data.error || 'Failed to create stock out.');
      }
    })
    .catch(() => alert('Server error.'))
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Create';
    });
});

form.addEventListener('keydown', function (e) {
  if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
    e.preventDefault();
    submitBtn.click();
  }
});
