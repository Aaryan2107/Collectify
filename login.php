<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
  header('Location: exp1.php');
    exit;
}

$error = '';
$next = $_GET['next'] ?? 'exp1.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $next = $_POST['next'] ?? 'exp1.php';

    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $error = 'Invalid email or password.';
    } else {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: ' . $next);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Collectify</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body>
<header>
  <h1><span>Collect</span>ify</h1>
  <nav>
    <a href="exp1.php">Home</a>
    <a href="login.php" class="active">Login</a>
    <a href="register.php">Register</a>
    <a href="cart.php">Cart</a>
  </nav>
</header>

<div class="container">
  <div class="page-hero">
    <h2>Welcome Back</h2>
    <p>Login to add products to cart and buy now.</p>
  </div>

  <?php if ($error): ?>
    <p style="color:#f87171; margin-bottom:16px;"><?= h($error) ?></p>
  <?php endif; ?>

  <form method="post" action="login.php">
    <input type="hidden" name="next" value="<?= h($next) ?>">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <input type="submit" value="Login">
  </form>
</div>

</body>
</html>
