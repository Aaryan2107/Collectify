<?php
require_once __DIR__ . '/auth.php';
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
    <a class="lane-card" href="hotwheels.php">
      <img src="images/hotwheels.webp" alt="Hot Wheels Lane">
      <div class="lane-overlay">
        <h3>Hot Wheels</h3>
        <p>Classic lines, premium releases, and chase variants.</p>
        <span>Open Lane</span>
      </div>
    </a>
    <a class="lane-card" href="minigt.php">
      <img src="images/minigt.webp" alt="Mini GT Lane">
      <div class="lane-overlay">
        <h3>Mini GT</h3>
        <p>Licensed 1:64 castings with precision details.</p>
        <span>Open Lane</span>
      </div>
    </a>
    <a class="lane-card" href="lego.php">
      <img src="images/lego.webp" alt="LEGO Lane">
      <div class="lane-overlay">
        <h3>LEGO</h3>
        <p>Display-ready builds from technical to creative series.</p>
        <span>Open Lane</span>
      </div>
    </a>
  </section>

</div>

</body>
</html>