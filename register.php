<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
  header('Location: exp1.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || strlen($name) < 3) {
        $error = 'Name must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Email already registered. Please login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $success = 'Account created successfully. You can login now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Collectify</title>
<link rel="stylesheet" href="exp1.css">
</head>
<body>
<header>
  <h1><a href="exp1.php"><span>Collect</span>ify</a></h1>
  <nav>
    <a href="exp1.php">Home</a>
    <a href="login.php">Login</a>
    <a href="register.php" class="active">Sign Up</a>
    <a href="cart.php">Cart</a>
  </nav>
  <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </button>
</header>

<div class="container">
  <div class="page-hero">
    <h2>Create Account</h2>
    <p>Register to save your cart and place orders.</p>
  </div>

  <?php if ($error): ?>
    <p style="color:#f87171; margin-bottom:16px;"><?= h($error) ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color:#34d399; margin-bottom:16px;"><?= h($success) ?></p>
  <?php endif; ?>

  <form method="post" action="register.php">
    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <input type="submit" value="Create Account">
  </form>
</div>

<script src="exp1.js"></script>
</body>
</html>
