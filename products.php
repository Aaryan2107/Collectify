<?php
// admin/products.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

$message = isset($_GET['msg']) ? $_GET['msg'] : '';
$error   = isset($_GET['err']) ? $_GET['err'] : '';

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int)$_POST['id'];
    $pdo->prepare('UPDATE products SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
    header('Location: products.php'); exit();
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    header('Location: products.php?msg=Product+deleted'); exit();
}

// Filter
$catFilter = (int)($_GET['cat'] ?? 0);
$search    = trim($_GET['q'] ?? '');

$sql = 'SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1';
$params = [];
if ($catFilter) { $sql .= ' AND p.category_id = ?'; $params[] = $catFilter; }
if ($search)    { $sql .= ' AND p.name LIKE ?';      $params[] = "%$search%"; }
$sql .= ' ORDER BY c.sort_order, p.sort_order, p.name';
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll(PDO::FETCH_ASSOC);

layout_head('Products');
layout_sidebar('products', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div><h1>Products</h1><div class="breadcrumb">Manage your product catalogue</div></div>
    <a href="add_product.php" class="btn btn-primary">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
      Add Product
    </a>
  </div>
  <div class="content">

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- FILTERS -->
    <form method="GET" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
      <input type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
      <select name="cat" style="min-width:180px;">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-ghost">Filter</button>
      <?php if ($search || $catFilter): ?>
        <a href="products.php" class="btn btn-ghost">Clear</a>
      <?php endif; ?>
    </form>

    <div class="card">
      <div class="card-header">
        <h3>Products (<?= count($products) ?>)</h3>
      </div>
      <table>
        <thead>
          <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td>
            <?php if ($p['image']): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" class="product-thumb" alt="">
            <?php else: ?>
              <div class="product-thumb-placeholder">📦</div>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= htmlspecialchars($p['name']) ?></strong><br>
            <small style="color:var(--muted);"><?= htmlspecialchars($p['product_id']) ?></small>
          </td>
          <td><?= htmlspecialchars($p['cat_name']) ?></td>
          <td>₹<?= number_format($p['price'],2) ?></td>
          <td>
            <span class="badge <?= $p['stock'] == 0 ? 'badge-danger' : ($p['stock'] < 5 ? 'badge-warning' : 'badge-success') ?>">
              <?= $p['stock'] ?>
            </span>
          </td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="border:none;cursor:pointer;background:none;padding:0;">
                <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span>
              </button>
            </form>
          </td>
          <td style="display:flex;gap:6px;align-items:center;">
            <a href="add_product.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete product?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">No products found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php layout_end(); ?>
