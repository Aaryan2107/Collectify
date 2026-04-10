<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/product_catalog.php';

$homeCategories = get_home_categories($pdo);
$laneCategories = [];
foreach ($homeCategories as $category) {
  $page = catalog_page_for_slug($category['slug']);
  if ($page) {
    $laneCategories[] = [
      'name' => $category['name'],
      'slug' => $category['slug'],
      'description' => $category['description'] ?: 'Browse our latest collection.',
      'image' => $category['image'] ?: default_category_image($category['slug']),
      'page' => $page,
      'count' => (int) $category['product_count'],
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Collectify</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body class="home-page">

<header>
  <h1><span>Collect</span>ify</h1>
  <nav>
    <a href="exp1.php" class="active">Home</a>
    <?php if (is_logged_in()): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Sign Up</a>
    <?php endif; ?>
    <a href="cart.php">Cart</a>
  </nav>
</header>

<div class="container">

  <div class="section-title">Explore Lanes</div>
  <section class="lane-grid">
    <?php foreach ($laneCategories as $lane): ?>
    <a class="lane-card" href="<?= h($lane['page']) ?>">
      <img src="<?= h($lane['image']) ?>" alt="<?= h($lane['name']) ?> Lane">
      <div class="lane-overlay">
        <h3><?= h($lane['name']) ?></h3>
        <p><?= h($lane['description']) ?></p>
        <span><?= $lane['count'] ?> Products</span>
      </div>
    </a>
    <?php endforeach; ?>
  </section>

  <?php if (empty($laneCategories)): ?>
    <p style="color:var(--muted);margin-top:18px;">No categories available yet. Add categories from the admin panel to populate the home page.</p>
  <?php endif; ?>

</div>

</body>
</html>