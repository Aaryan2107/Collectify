<?php
require_once __DIR__ . '/auth.php';
require_login();

$userId = (int) $_SESSION['user_id'];
$ordersStmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$ordersStmt->execute([$userId]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$itemsStmt = $pdo->prepare('SELECT order_id, product_name, quantity, price FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?) ORDER BY order_id DESC');
$itemsStmt->execute([$userId]);
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($orderItems as $item) {
    $grouped[$item['order_id']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders | Collectify</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body>
<header>
  <h1><span>Collect</span>ify</h1>
  <nav>
    <a href="exp1.php">Home</a>
    <a href="cart.php">Cart</a>
    <a href="orders.php" class="active">Orders</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <div class="page-hero">
    <h2>Your Orders</h2>
    <p>Track purchases placed from Buy Now and cart checkout.</p>
  </div>

  <?php if (isset($_GET['ok']) && $_GET['ok'] === '1'): ?>
    <p style="color:#34d399; margin-bottom:18px;">Order placed successfully.</p>
  <?php endif; ?>

  <?php if (!$orders): ?>
    <p style="color:var(--muted);">No orders yet. Add products and checkout.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div class="product-card" style="margin-bottom:16px;">
        <div class="product-meta">
          <h3>Order #<?= (int) $order['id'] ?></h3>
          <p>Status: <?= h($order['status']) ?> | Date: <?= h($order['created_at']) ?></p>
          <p style="color:#4ade80;">Total: Rs. <?= number_format((float) $order['total_amount'], 2) ?></p>

          <ul>
            <?php foreach ($grouped[$order['id']] ?? [] as $item): ?>
              <li><?= h($item['product_name']) ?> x <?= (int) $item['quantity'] ?> (Rs. <?= number_format((float) $item['price'], 2) ?>)</li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>
