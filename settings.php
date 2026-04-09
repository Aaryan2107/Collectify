<?php
// admin/settings.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $_SESSION['admin_id']]);
            $message = 'Password changed successfully!';
        }
    }

    if ($action === 'add_admin') {
        $uname = trim($_POST['new_username'] ?? '');
        $email = trim($_POST['new_email'] ?? '');
        $pass  = $_POST['new_admin_pass'] ?? '';

        if ($uname === '' || $pass === '') {
            $error = 'Username and password are required.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO admin_users (username, email, password_hash) VALUES (?,?,?)')->execute([$uname,$email,$hash]);
                $message = "Admin user \"$uname\" created!";
            } catch (PDOException $e) {
                $error = 'Username already exists.';
            }
        }
    }
}

$admins = $pdo->query('SELECT id, username, email, created_at FROM admin_users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

layout_head('Settings');
layout_sidebar('settings', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div><h1>Settings</h1><div class="breadcrumb">Account & Admin Management</div></div>
  </div>
  <div class="content">

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      <div class="form-card">
        <h3>Change Password</h3>
        <form method="POST">
          <input type="hidden" name="action" value="change_password">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="8">
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
      </div>

      <div class="form-card">
        <h3>Add Admin User</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add_admin">
          <div class="form-group">
            <label>Username *</label>
            <input type="text" name="new_username" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="new_email">
          </div>
          <div class="form-group">
            <label>Password * (min 8 chars)</label>
            <input type="password" name="new_admin_pass" required minlength="8">
          </div>
          <button type="submit" class="btn btn-primary">Create Admin</button>
        </form>
      </div>

    </div>

    <div class="card">
      <div class="card-header"><h3>Admin Users</h3></div>
      <table>
        <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
        <tr>
          <td><?= $a['id'] ?></td>
          <td><strong><?= htmlspecialchars($a['username']) ?></strong>
            <?= $a['id'] == $_SESSION['admin_id'] ? '<span class="badge badge-success" style="margin-left:6px">You</span>' : '' ?>
          </td>
          <td style="color:var(--muted)"><?= htmlspecialchars($a['email'] ?? '—') ?></td>
          <td style="color:var(--muted);font-size:0.82rem;"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
          <td>
            <?php if ($a['id'] != $_SESSION['admin_id']): ?>
            <form method="POST" onsubmit="return confirm('Remove this admin?')">
              <input type="hidden" name="action" value="delete_admin">
              <input type="hidden" name="admin_id" value="<?= $a['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Remove</button>
            </form>
            <?php else: ?>
            <span style="color:var(--muted);font-size:0.8rem;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php layout_end(); ?>
