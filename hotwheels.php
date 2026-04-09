<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/product_catalog.php';

$ids = ['hw-001','hw-002','hw-003','hw-004','hw-005','hw-006','hw-007','hw-008','hw-009','hw-010','hw-011','hw-012'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Collectify | Hot Wheels</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body>
<header>
  <h1><span>Collect</span>ify</h1>
  <nav>
    <a href="exp1.php">Home</a>
    <span class="nav-separator" aria-hidden="true"></span>
    <a href="cart.php">Cart</a>
    <?php if (is_logged_in()): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>

<div class="category-bar">
  <a href="hotwheels.php" class="cat-item active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
    Hot Wheels
  </a>
  <a href="minigt.php" class="cat-item">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 17v2M18 17v2"/></svg>
    Mini GT
  </a>
  <a href="lego.php" class="cat-item">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="1"/><path d="M7 11V7a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v4"/><circle cx="8" cy="8" r="1" fill="currentColor"/><circle cx="12" cy="8" r="1" fill="currentColor"/><circle cx="16" cy="8" r="1" fill="currentColor"/></svg>
    LEGO
  </a>
</div>

<div class="container">
  <div class="page-hero">
    <h2>Hot Wheels Collection</h2>
    <p>Browse premium castings, team transports, and treasure hunts for every collector shelf.</p>
  </div>

  <div class="section-title">All Hot Wheels Products</div>
  <div class="product-grid">
    <?php foreach ($ids as $id): $product = $products[$id]; ?>
      <div class="product-card">
        <img src="<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
        <div class="product-meta">
          <h3><?= h($product['name']) ?></h3>
          <p><?= h($product['category']) ?> collectible</p>
          <div class="product-price">Rs. <?= number_format((float) $product['price'], 2) ?></div>

          <form method="post" action="add_to_cart.php" class="action-row">
            <input type="hidden" name="product_id" value="<?= h($id) ?>">
            <input type="hidden" name="redirect" value="hotwheels.php">
            <button type="submit" class="btn-secondary">Add to Cart</button>
          </form>

          <form method="post" action="buy_now.php" class="action-row">
            <input type="hidden" name="product_id" value="<?= h($id) ?>">
            <button type="submit" class="btn-buy">Buy Now</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>