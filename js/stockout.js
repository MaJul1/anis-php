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
