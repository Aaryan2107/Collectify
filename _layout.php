<?php
// admin/_layout.php
// Usage: include after auth_check, call layout_head($title), layout_sidebar($active), layout_end()

function layout_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> — Collectify Admin</title>
<link rel="stylesheet" href="exp1.css">
<style>
:root {
  --bg: #0d1117;
  --sidebar-bg: #161b27;
  --card-bg: #1e2535;
  --border: #2a3347;
  --accent: #f0a500;
  --accent2: #3b82f6;
  --text: #e2e8f0;
  --muted: #8b98b1;
  --danger: #ef4444;
  --success: #22c55e;
  --warning: #f59e0b;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Space Grotesk', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

/* SIDEBAR */
.sidebar {
  width: 240px; min-height: 100vh; background: var(--sidebar-bg);
  border-right: 1px solid var(--border); display: flex; flex-direction: column;
  position: fixed; top: 0; left: 0; z-index: 100;
}
.sidebar-toggle {
  display: none;
  position: fixed;
  top: 12px;
  left: 12px;
  width: 42px;
  height: 42px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: #121724;
  color: var(--text);
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 5px;
  cursor: pointer;
  z-index: 260;
}
.sidebar-toggle span {
  display: block;
  width: 18px;
  height: 2px;
  border-radius: 2px;
  background: currentColor;
  transition: transform 0.2s ease, opacity 0.2s ease;
}
body.sidebar-open .sidebar-toggle span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
body.sidebar-open .sidebar-toggle span:nth-child(2) { opacity: 0; }
body.sidebar-open .sidebar-toggle span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(2,6,23,0.6);
  z-index: 210;
}
.sidebar-logo { padding: 24px 20px; border-bottom: 1px solid var(--border); }
.sidebar-logo h2 { font-size: 1.4rem; font-weight: 800; color: #fff; font-family: 'Rajdhani', sans-serif; letter-spacing: 0.8px; }
.sidebar-logo h2 span { color: var(--accent); }
.sidebar-logo p { font-size: 0.7rem; color: var(--muted); margin-top: 2px; text-transform: uppercase; letter-spacing: 1px; }
.sidebar nav {
  display: block;
  padding: 16px 12px;
  flex: 1;
}
.nav-section { font-size: 0.65rem; font-weight: 700; color: var(--muted); text-transform: uppercase;
               letter-spacing: 1px; padding: 12px 8px 6px; }
.sidebar nav .nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  color: var(--muted);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0;
  text-transform: none;
  margin-bottom: 2px;
  transition: all 0.15s;
}
.sidebar nav .nav-item:hover { background: rgba(240,165,0,0.1); color: #fff; }
.sidebar nav .nav-item.active { background: rgba(240,165,0,0.16); color: var(--accent); }
.sidebar nav .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
.sidebar-footer { padding: 16px 12px; border-top: 1px solid var(--border); }
.admin-badge { display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(255,255,255,0.04); border-radius: 8px; }
.admin-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--accent); color: #0d1117; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
.admin-info small { display: block; font-size: 0.65rem; color: var(--muted); }
.admin-info span { font-size: 0.8rem; font-weight: 600; }
a.logout-link { display: block; text-align: center; margin-top: 8px; font-size: 0.78rem; color: var(--muted); text-decoration: none; }
a.logout-link:hover { color: var(--danger); }

/* MAIN */
.main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }
.topbar { padding: 16px 28px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #121724; }
.topbar h1 { font-size: 1.3rem; font-weight: 700; font-family: 'Rajdhani', sans-serif; letter-spacing: 0.6px; }
.topbar .breadcrumb { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }
.content { padding: 28px; flex: 1; }

/* CARDS */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px,1fr)); gap: 16px; margin-bottom: 28px; }
.stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
.stat-card .label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.stat-card .value { font-size: 2rem; font-weight: 800; }
.stat-card .sub { font-size: 0.78rem; color: var(--muted); margin-top: 4px; }

/* TABLE */
.card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
.card-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.card-header h3 { font-size: 0.95rem; font-weight: 700; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,0.02); }
.product-thumb { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; background: rgba(255,255,255,0.08); }
.product-thumb-placeholder { width: 44px; height: 44px; border-radius: 8px; background: rgba(124,106,247,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

/* BADGES */
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
.badge-success { background: rgba(34,197,94,0.15); color: #86efac; }
.badge-danger  { background: rgba(239,68,68,0.15);  color: #fca5a5; }
.badge-warning { background: rgba(245,158,11,0.15); color: #fcd34d; }

/* BUTTONS */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
       border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
       border: none; text-decoration: none; transition: opacity 0.15s, transform 0.1s; }
.btn:hover { opacity: 0.88; transform: translateY(-1px); }
.btn:active { transform: translateY(0); }
.btn-primary { background: linear-gradient(135deg, var(--accent), #fbbf24); color: #0d1117; }
.btn-danger  { background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
.btn-ghost   { background: rgba(255,255,255,0.06); color: var(--text); border: 1px solid var(--border); }
.btn-sm      { padding: 5px 10px; font-size: 0.78rem; }

/* FORMS */
.form-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 28px; margin-bottom: 24px; }
.form-card h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.form-group { margin-bottom: 18px; }
.form-group.full { grid-column: 1 / -1; }
label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
input[type=text], input[type=number], input[type=email], input[type=password],
select, textarea {
  width: 100%; padding: 10px 14px;
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--border);
  border-radius: 8px; color: var(--text); font-size: 0.9rem; outline: none;
  transition: border-color 0.2s;
}
input:focus, select:focus, textarea:focus { border-color: var(--accent); }
select option { background: var(--card-bg); }
textarea { resize: vertical; min-height: 90px; }
.upload-zone { border: 2px dashed var(--border); border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; transition: border-color 0.2s; }
.upload-zone:hover { border-color: var(--accent); }
.upload-zone p { color: var(--muted); font-size: 0.85rem; margin-top: 8px; }
.img-preview { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; margin: 10px auto 0; display: block; }
.form-actions { display: flex; gap: 12px; margin-top: 8px; }

/* ALERTS */
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; }
.alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.25); color: #86efac; }
.alert-error   { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }

/* MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 500; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 28px; width: 100%; max-width: 460px; }
.modal h3 { margin-bottom: 16px; font-size: 1rem; }
.modal .form-group { margin-bottom: 16px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

@media (max-width: 768px) {
  body { flex-direction: column; }
  .sidebar-toggle { display: inline-flex; }
  .sidebar-overlay { display: none; }
  body.sidebar-open .sidebar-overlay { display: block; }
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    min-height: 100vh;
    height: 100vh;
    transform: translateX(-100%);
    transition: transform 0.22s ease;
    border-right: 1px solid var(--border);
    border-bottom: none;
    z-index: 230;
    overflow-y: auto;
  }
  body.sidebar-open .sidebar {
    transform: translateX(0);
  }
  body.sidebar-open .sidebar-logo {
    padding-left: 64px;
  }
  body.sidebar-open {
    overflow: hidden;
  }
  .sidebar nav {
    padding: 10px;
  }
  .sidebar-footer {
    padding: 12px 10px;
  }
  .main { margin-left: 0; width: 100%; }
  .topbar {
    padding: 14px 18px 14px 62px;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  .content { padding: 18px; }
  .form-grid { grid-template-columns: 1fr; }
  .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .card-header { flex-direction: column; align-items: flex-start; gap: 10px; }
  .form-actions { flex-direction: column; }
}
</style>
</head>
<body>
<?php } // end layout_head

function layout_sidebar(string $active, string $admin_username): void { ?>
<button class="sidebar-toggle" type="button" aria-label="Toggle sidebar" aria-expanded="false">
  <span></span>
  <span></span>
  <span></span>
</button>
<div class="sidebar-overlay"></div>
<div class="sidebar">
  <div class="sidebar-logo">
    <h2><a href="index.php">Collect<span>ify</span></a></h2>
    <p>Admin Panel</p>
  </div>
  <nav>
    <div class="nav-section">Main</div>
    <a href="index.php" class="nav-item <?= $active==='dashboard' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Dashboard
    </a>
    <div class="nav-section">Catalogue</div>
    <a href="categories.php" class="nav-item <?= $active==='categories' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
      Categories
    </a>
    <a href="products.php" class="nav-item <?= $active==='products' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      Products
    </a>
    <a href="add_product.php" class="nav-item <?= $active==='add_product' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add Product
    </a>
    <div class="nav-section">Store</div>
    <a href="orders.php" class="nav-item <?= $active==='orders' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Orders
    </a>
    <div class="nav-section">Account</div>
    <a href="settings.php" class="nav-item <?= $active==='settings' ? 'active' : '' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Settings
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="admin-badge">
      <div class="admin-avatar"><?= strtoupper(substr($admin_username,0,1)) ?></div>
      <div class="admin-info">
        <span><?= htmlspecialchars($admin_username) ?></span>
        <small>Administrator</small>
      </div>
    </div>
    <a href="admin_logout.php" class="logout-link">← Sign out</a>
  </div>
</div>
<?php } // end layout_sidebar

function layout_end(): void { ?>
<script>
(function () {
  var toggle = document.querySelector('.sidebar-toggle');
  var overlay = document.querySelector('.sidebar-overlay');
  if (!toggle) return;

  function setOpen(open) {
    document.body.classList.toggle('sidebar-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  toggle.addEventListener('click', function () {
    setOpen(!document.body.classList.contains('sidebar-open'));
  });

  if (overlay) {
    overlay.addEventListener('click', function () {
      setOpen(false);
    });
  }

  document.querySelectorAll('.sidebar .nav-item, .sidebar .logout-link').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.innerWidth <= 768) {
        setOpen(false);
      }
    });
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      setOpen(false);
    }
  });
})();
</script>
</body>
</html>
<?php } // end layout_end
