<?php
// admin/index.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

// Stats
$totalProducts  = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalOrders    = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalRevenue   = $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != "cancelled"')->fetchColumn();
$recentOrders   = $pdo->query('SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$lowStock       = $pdo->query('SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock < 5 ORDER BY p.stock ASC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);

layout_head('Dashboard');
layout_sidebar('dashboard', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div>
      <h1>Dashboard</h1>
      <div class="breadcrumb">Welcome back, <?= htmlspecialchars($admin_username) ?>!</div>
    </div>
    <a href="add_product.php" class="btn btn-primary">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
      Add Product
    </a>
  </div>
  <div class="content">

    <div class="stat-grid">
      <div class="stat-card">
        <div class="label">Total Products</div>
        <div class="value"><?= $totalProducts ?></div>
        <div class="sub">Across all categories</div>
      </div>
      <div class="stat-card">
        <div class="label">Categories</div>
        <div class="value"><?= $totalCategories ?></div>
        <div class="sub"><a href="categories.php" style="color:var(--accent);text-decoration:none;">Manage →</a></div>
      </div>
      <div class="stat-card">
        <div class="label">Total Orders</div>
        <div class="value"><?= $totalOrders ?></div>
        <div class="sub">All time</div>
      </div>
      <div class="stat-card">
        <div class="label">Revenue</div>
        <div class="value" style="font-size:1.5rem;">₹<?= number_format($totalRevenue, 2) ?></div>
        <div class="sub">Excluding cancelled</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      <div class="card">
        <div class="card-header">
          <h3>Recent Orders</h3>
          <a href="orders.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <table>
          <thead><tr><th>#</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td>#<?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['user_name']) ?></td>
            <td>₹<?= number_format($order['total_amount'],2) ?></td>
            <td>
              <?php
              $sc = match($order['status']) {
                'placed'    => 'badge-warning',
                'delivered' => 'badge-success',
                'cancelled' => 'badge-danger',
                default     => 'badge-warning'
              };
              ?>
              <span class="badge <?= $sc ?>"><?= htmlspecialchars($order['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentOrders)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">No orders yet</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>⚠️ Low Stock Products</h3>
          <a href="products.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <table>
          <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['cat_name']) ?></td>
            <td><span class="badge <?= $p['stock']==0 ? 'badge-danger' : 'badge-warning' ?>"><?= $p['stock'] ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($lowStock)): ?>
          <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px;">All stocked up! 🎉</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
<?php layout_end(); ?>
