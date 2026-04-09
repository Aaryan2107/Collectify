<?php
require_once __DIR__ . '/auth.php';
require_login();

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_id'])) {
        $remove = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
        $remove->execute([(int) $_POST['remove_id'], $userId]);
    }

    if (isset($_POST['update_id'], $_POST['quantity'])) {
        $quantity = max(1, (int) $_POST['quantity']);
        $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?');
        $update->execute([$quantity, (int) $_POST['update_id'], $userId]);
    }

    header('Location: cart.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, product_name, product_image, price, quantity FROM cart_items WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$userId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $item) {
    $total += ((float) $item['price']) * ((int) $item['quantity']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart | Collectify</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body>
<header>
  <h1><span>Collect</span>ify</h1>
  <nav>
    <a href="exp1.php">Home</a>
    <span class="nav-separator" aria-hidden="true"></span>
    <a href="orders.php">Orders</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="category-bar">
  <a href="hotwheels.php" class="cat-item">
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
    <h2><?= h(current_user_name()) ?>'s Cart</h2>
    <p>Review your items, update quantity, and checkout.</p>
  </div>

  <?php if (!$items): ?>
    <p style="color:var(--muted);">Your cart is empty. Explore categories and add products.</p>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($items as $item): ?>
        <div class="product-card">
          <img src="<?= h($item['product_image']) ?>" alt="<?= h($item['product_name']) ?>">
          <div class="product-meta">
            <h3><?= h($item['product_name']) ?></h3>
            <p>Unit Price: Rs. <?= number_format((float) $item['price'], 2) ?></p>
            <p>Total: Rs. <?= number_format((float) $item['price'] * (int) $item['quantity'], 2) ?></p>

            <form method="post" action="cart.php" style="margin-top:8px; background:transparent; border:none; padding:0; max-width:100%;">
              <input type="hidden" name="update_id" value="<?= (int) $item['id'] ?>">
              <label>Quantity</label>
              <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" style="max-width:140px;">
              <input type="submit" value="Update Qty" style="margin-top:10px;">
            </form>

            <form method="post" action="cart.php" style="margin-top:8px; background:transparent; border:none; padding:0; max-width:100%;">
              <input type="hidden" name="remove_id" value="<?= (int) $item['id'] ?>">
              <button type="submit" class="btn-secondary">Remove</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section-title">Order Summary</div>
    <p style="font-size:1.2rem; color:#4ade80; font-weight:700; margin-bottom:18px;">Grand Total: Rs. <?= number_format($total, 2) ?></p>
    <form method="post" action="checkout.php" style="background:transparent; border:none; padding:0; max-width:100%;">
      <button type="submit" class="btn-buy">Checkout All</button>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
