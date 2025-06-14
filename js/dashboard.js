// Expired Stocks Warning: Enable/disable and check all functionality

document.addEventListener('DOMContentLoaded', function() {
  // Find the Expired Stocks Warning table and the Checked button
  const expiredTable = document.getElementById('expired-restock-table');
  const checkedBtn = document.getElementById('check-expired-button');
  if (!expiredTable || !checkedBtn) return;

  // Check if there are any data rows (not the empty message row)
  const hasRows = Array.from(expiredTable.querySelectorAll('tbody tr')).some(row => !row.querySelector('td[colspan]'));
  checkedBtn.disabled = !hasRows;

  checkedBtn.addEventListener('click', function() {
    if (checkedBtn.disabled) return;
    if (!confirm('Are you sure you want to check all expired stocks in the list?')) return;
    // Collect expiration dates and product names from the table
    const rows = Array.from(expiredTable.querySelectorAll('tbody tr')).filter(row => !row.querySelector('td[colspan]'));
    const items = rows
      .map(row => {
        const id = row.getAttribute('data-restock-detail-id');
        return id && !isNaN(Number(id)) && Number(id) > 0 ? { id: Number(id) } : null;
      })
      .filter(item => item !== null);
    if (items.length === 0) return;
    // Send AJAX request to mark all as checked
    fetch('Persistence/RestockRepository/checkAllExpired.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.error || 'Failed to check expired stocks.');
      }
    })
    .catch(() => alert('Server error.'));
  });
});

// Print Checklist functionality for Expired Stocks Warning
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.querySelector('.btn.btn-secondary');
    // Find the Expired Stocks Warning table
    const expiredTable = document.getElementById('expired-restock-table');
    if (!printBtn || !expiredTable) return;
    printBtn.addEventListener('click', function() {
      // Collect data rows (skip empty message row)
      const rows = Array.from(expiredTable.querySelectorAll('tbody tr')).filter(row => !row.querySelector('td[colspan]'));
      if (rows.length === 0) {
        alert('No stocks to print.');
        return;
      }
      // Build printable HTML
      let html = `
        <html>
        <head>
          <title>Expired Stocks Checklist</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #333; padding: 4px; text-align: left; }
            th { background: #eee; }
            .box { width: 10px; height: 10px; border: 1px solid #333; display: block; margin: 0 auto; }
          </style>
        </head>
        <body>
          <h2>Expired/Near Expired Stocks Checklist</h2>
          <table>
            <thead>
              <tr><th>Name</th><th>Expiration Date</th><th>Restock Count</th><th>Box</th></tr>
            </thead>
            <tbody>
      `;
      rows.forEach(row => {
        const tds = row.querySelectorAll('td');
        const name = tds[0]?.textContent.trim() || '';
        const expiration = tds[1]?.textContent.trim() || '';
        const count = tds[2]?.textContent.trim() || '';
        html += `<tr><td>${name}</td><td>${expiration}</td><td>${count}</td><td><div class='box'></div></td></tr>`;
      });
      html += `</tbody></table></body></html>`;
      // Open print window
      const printWindow = window.open('', '', 'width=800,height=600');
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    });
  });
})();
