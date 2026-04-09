<?php
require_once __DIR__ . '/auth.php';

$mode = ($_GET['role'] ?? $_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

if ($mode === 'admin' && !empty($_SESSION['admin_logged_in'])) {
  header('Location: index.php');
  exit;
}

if ($mode === 'user' && is_logged_in()) {
  header('Location: exp1.php');
  exit;
}

$error = '';
$msg = '';
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$next = trim($_GET['next'] ?? $_POST['next'] ?? 'exp1.php');

if ($next === '' || str_contains($next, '://') || str_starts_with($next, '//')) {
  $next = 'exp1.php';
}

if (isset($_GET['msg']) && $mode === 'admin') {
  if ($_GET['msg'] === 'timeout') {
    $msg = 'Session timed out. Please log in again.';
  } elseif ($_GET['msg'] === 'logout') {
    $msg = 'You have been logged out.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = $_POST['password'] ?? '';

  if ($mode === 'admin') {
    if ($username === '' || $password === '') {
      $error = 'Please fill in username and password.';
    } else {
      $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');
      $stmt->execute([$username]);
      $admin = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_last_activity'] = time();
        header('Location: index.php');
        exit;
      }

      $error = 'Invalid admin username or password.';
    }
  } else {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
      $error = 'Enter a valid email and password.';
    } else {
      $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: ' . $next);
        exit;
      }

      $error = 'Invalid email or password.';
    }
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
<style>
  .auth-toggle {
    display: inline-flex !important;
    position: relative;
    background: rgba(13, 17, 23, 0.92);
    border: 1px solid #2a3347;
    border-radius: 999px;
    margin-bottom: 24px;
    padding: 5px;
    gap: 4px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), 0 12px 30px rgba(0,0,0,0.35);
  }

  .auth-toggle .auth-toggle-btn {
    appearance: none;
    border: 1px solid transparent;
    background: transparent;
    color: #8b98b1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    padding: 10px 20px;
    border-radius: 999px;
    cursor: pointer;
    font: 700 0.88rem/1 'Rajdhani', sans-serif;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    transition: all 0.2s ease;
  }

  .auth-toggle .auth-toggle-btn svg {
    width: 15px;
    height: 15px;
    opacity: 0.78;
  }

  .auth-toggle .auth-toggle-btn:hover {
    color: #e2e8f0;
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
  }

  .auth-toggle .auth-toggle-btn.active {
    color: #0d1117;
    background: linear-gradient(135deg, #f0a500, #fbbf24 60%, #fde68a);
    border-color: rgba(251,191,36,0.5);
    box-shadow: 0 6px 18px rgba(240,165,0,0.45), inset 0 1px 0 rgba(255,255,255,0.25);
  }

  .auth-toggle .auth-toggle-btn.active svg {
    opacity: 1;
  }
</style>
</head>
<body class="auth-page">
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
    <h2>Login Portal</h2>
    <p>Switch between Customer and Admin login below.</p>
  </div>

  <div class="auth-toggle" role="tablist" aria-label="Login mode">
    <button type="button" class="auth-toggle-btn <?= $mode === 'user' ? 'active' : '' ?>"
            role="tab" aria-selected="<?= $mode === 'user' ? 'true' : 'false' ?>"
            onclick="window.location.href='login.php?role=user<?= $next !== 'exp1.php' ? '&next=' . urlencode($next) : '' ?>'">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
      </svg>
      Customer Login
    </button>
    <button type="button" class="auth-toggle-btn <?= $mode === 'admin' ? 'active' : '' ?>"
            role="tab" aria-selected="<?= $mode === 'admin' ? 'true' : 'false' ?>"
            onclick="window.location.href='login.php?role=admin'">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2l7 4v6c0 5-3.5 9.5-7 11C8.5 21.5 5 17 5 12V6z"/>
      </svg>
      Admin Login
    </button>
  </div>

  <?php if ($msg): ?>
    <p class="alert-inline alert-info"><?= h($msg) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="alert-inline alert-error"><?= h($error) ?></p>
  <?php endif; ?>

  <form method="post" action="login.php" class="auth-form">
    <input type="hidden" name="role" value="<?= $mode ?>">

    <?php if ($mode === 'user'): ?>
      <input type="hidden" name="next" value="<?= h($next) ?>">
      <label>Email</label>
      <input type="email" name="email" value="<?= h($email) ?>" required>
    <?php else: ?>
      <label>Admin Username</label>
      <input type="text" name="username" value="<?= h($username) ?>" required>
    <?php endif; ?>

    <label>Password</label>
    <input type="password" name="password" required>

    <input type="submit" value="<?= $mode === 'admin' ? 'Sign In as Admin' : 'Sign In as Customer' ?>">

  </form>
</div>
</body>
</html>