// js/sidebar.js
// Usage: Place <div id="sidebar"></div> where you want the sidebar, and include this script at the end of <body>.
// Set window.activeSidebar = 'dashboard' | 'product' | 'restock' | 'stockout' before including this script.
document.addEventListener('DOMContentLoaded', function () {
  const active = window.activeSidebar || '';
  function isActive(page) {
    return active === page ? 'bg-primary text-white' : 'link-body-emphasis';
  }
  function isIconActive(page) {
    return active === page ? 'text-white' : 'link-body-emphasis';
  }
  const sidebarHTML = `
    <div class="d-flex flex-column offcanvas-md offcanvas-start p-3 position-fixed" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 235px; height: 100vh; background-color: var(--bs-secondary-bg) !important;">
      <a class="d-flex align-items-center mb-0 mb-md-1 link-body-emphasis text-decoration-none" href="index.php">
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
      <div class="mb-1 p-2 rounded ${isActive('stockout')}">
        <a class="${isActive('stockout')} text-decoration-none d-flex" href="stockout.php">
          <i class="bi bi-box-arrow-up me-2 ${isIconActive('stockout')}"></i>
          Stock Out
        </a>
      </div>
      <div class="dropdown mb-1 p-2 rounded mb-auto">
        <a class="dropdown-toggle text-decoration-none d-flex link-body-emphasis" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-moon-stars me-2"></i>
          Theme
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Light</a></li>
          <li><a class="dropdown-item" href="#">Dark</a></li>
        </ul>
      </div>
      <div class="d-none d-md-block">
        <hr class="mb-1">
        <a href="user.php" class="d-flex align-items-center text-decoration-none link-body-emphasis ps-2 rounded">
          <i class="bi bi-person-circle fs-1 me-3"></i>
          <span id="sidebar-username">Hello</span>
        </a>      
      </div>
    </div>
    <div class="d-none d-md-block" style="min-width: 235px; height: 100%;"></div>
  `;
  const sidebarContainer = document.getElementById('sidebar');
  if (sidebarContainer) {
    sidebarContainer.innerHTML = sidebarHTML;
    // Add theme switcher functionality
    const themeDropdown = sidebarContainer.querySelectorAll('.dropdown-item');
    themeDropdown.forEach(function(item) {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const theme = this.textContent.trim().toLowerCase();
        if (theme === 'light' || theme === 'dark') {
          document.documentElement.setAttribute('data-bs-theme', theme);
          document.cookie = 'theme=' + theme + '; path=/; max-age=31536000'; // 1 year
          localStorage.setItem('bs-theme', theme);
        }
      });
    });
    // On load, apply saved theme from cookie or localStorage
    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    }
    const cookieTheme = getCookie('theme');
    const savedTheme = cookieTheme || localStorage.getItem('bs-theme');
    if (savedTheme === 'light' || savedTheme === 'dark') {
      document.documentElement.setAttribute('data-bs-theme', savedTheme);
    }
  }
  const username = window.sidebarUsername || null;
  if (username) {
    const userElem = document.getElementById('sidebar-username');
    if (userElem) userElem.textContent = `Hello ${username}`;
  }
});
