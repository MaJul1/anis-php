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
