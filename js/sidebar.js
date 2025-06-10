// js/sidebar.js
// Usage: Place <div id="sidebar"></div> where you want the sidebar, and include this script at the end of <body>.
// Set window.activeSidebar = 'dashboard' | 'product' | 'restock' | 'stockout' before including this script.
document.addEventListener('DOMContentLoaded', function () {
  const active = window.activeSidebar || '';
  function isActive(page) {
    return active === page ? 'bg-primary text-white' : 'text-dark';
  }
  function isIconActive(page) {
    return active === page ? 'text-white' : 'text-dark';
  }
  const sidebarHTML = `
    <div class="d-flex flex-column offcanvas-md offcanvas-start p-3 bg-light position-fixed" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 235px; height: 100vh">
      <a class="d-flex align-items-center mb-0 mb-md-1 link-dark text-decoration-none" href="index.php">
        <img src="assets/logo.png" class="rounded me-2" style="width: 60px; height: 60px"></img>
        <span class="fs-4 d-md-inline fw-semibold">ANIS</span>
      </a>
      <hr class="mt-1 mb-2"/>
      <div class="mb-1 p-2 rounded ${isActive('dashboard')}">
        <a class="${isActive('dashboard')} text-decoration-none d-flex" href="index.php">
          <i class="bi bi-speedometer2 me-2 ${isIconActive('dashboard')}"></i>
          Dashboard
        </a>
      </div>
      <div class="mb-1 p-2 rounded ${isActive('product')}">
        <a class="${isActive('product')} text-decoration-none d-flex" href="product.php">
          <i class="bi bi-box me-2 ${isIconActive('product')}"></i>
          Products
        </a>
      </div>
      <div class="mb-1 p-2 rounded ${isActive('restock')}">
        <a class="${isActive('restock')} text-decoration-none d-flex" href="restock.php">
          <i class="bi bi-box-arrow-in-down me-2 ${isIconActive('restock')}"></i>
          Restock
        </a>
      </div>
      <div class="mb-1 p-2 rounded mb-auto ${isActive('stockout')}">
        <a class="${isActive('stockout')} text-decoration-none d-flex" href="stockout.php">
          <i class="bi bi-box-arrow-up me-2 ${isIconActive('stockout')}"></i>
          Stock Out
        </a>
      </div>
      <hr class="mb-1">
      <a href="user.php" class="d-flex align-items-center text-decoration-none text-dark ps-2 rounded">
        <i class="bi bi-person-circle fs-1 me-3"></i>
        <span id="sidebar-username">Hello</span>
      </a>
    </div>
    <div class="d-none d-md-block" style="min-width: 235px; height: 100vh;"></div>
  `;
  const sidebarContainer = document.getElementById('sidebar');
  if (sidebarContainer) {
    sidebarContainer.innerHTML = sidebarHTML;
  }
  const username = window.sidebarUsername || null;
  if (username) {
    const userElem = document.getElementById('sidebar-username');
    if (userElem) userElem.textContent = `Hello ${username}`;
  }
});
