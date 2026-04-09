<?php
// admin/orders.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

$message = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'];
    $allowed = ['placed','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    }
    header('Location: orders.php?msg=Order+status+updated'); exit();
}

if (isset($_GET['msg'])) $message = $_GET['msg'];

$orders = $pdo->query('SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Get items for a specific order (AJAX or inline)
$expandId = (int)($_GET['expand'] ?? 0);
$orderItems = [];
if ($expandId) {
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$expandId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

layout_head('Orders');
layout_sidebar('orders', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div><h1>Orders</h1><div class="breadcrumb">View and manage customer orders</div></div>
  </div>
  <div class="content">

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h3>All Orders (<?= count($orders) ?>)</h3></div>
      <table>
        <thead><tr><th>#</th><th>Customer</th><th>Email</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td><?= htmlspecialchars($o['user_name']) ?></td>
          <td style="color:var(--muted);font-size:0.82rem;"><?= htmlspecialchars($o['user_email']) ?></td>
          <td>₹<?= number_format($o['total_amount'],2) ?></td>
          <td>
            <?php
            $sc = match($o['status']) {
              'placed'    => 'badge-warning',
              'processing'=> 'badge-warning',
              'shipped'   => 'badge-warning',
              'delivered' => 'badge-success',
              'cancelled' => 'badge-danger',
              default     => 'badge-warning'
            };
            ?>
            <span class="badge <?= $sc ?>"><?= htmlspecialchars($o['status']) ?></span>
          </td>
          <td style="color:var(--muted);font-size:0.82rem;"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick="toggleItems(<?= $o['id'] ?>)">View Items</button>
            <button class="btn btn-ghost btn-sm" onclick="openStatusModal(<?= $o['id'] ?>, '<?= $o['status'] ?>')">Status</button>
          </td>
        </tr>
        <tr id="items-<?= $o['id'] ?>" style="display:none">
          <td colspan="7" style="padding:0;">
            <div style="background:rgba(255,255,255,0.03);padding:16px 20px;border-top:1px solid var(--border);">
              <strong style="font-size:0.8rem;color:var(--muted);">ORDER ITEMS</strong>
              <table style="margin-top:10px;">
                <thead><tr><th>Image</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                <tbody id="items-body-<?= $o['id'] ?>">
                  <tr><td colspan="5" style="color:var(--muted);">Loading…</td></tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">No orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- STATUS MODAL -->
<div class="modal-overlay" id="statusModal">
  <div class="modal">
    <h3>Update Order Status</h3>
    <form method="POST">
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="id" id="status_order_id">
      <div class="form-group">
        <label>New Status</label>
        <select name="status" id="status_select">
          <option value="placed">Placed</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('statusModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open'); }));

function openStatusModal(id, status) {
  document.getElementById('status_order_id').value = id;
  document.getElementById('status_select').value = status;
  document.getElementById('statusModal').classList.add('open');
}

const loadedOrders = {};
function toggleItems(orderId) {
  const row = document.getElementById('items-' + orderId);
  if (row.style.display === 'none') {
    row.style.display = '';
    if (!loadedOrders[orderId]) {
      fetch('get_order_items.php?id=' + orderId)
        .then(r => r.json())
        .then(items => {
          loadedOrders[orderId] = true;
          const tbody = document.getElementById('items-body-' + orderId);
          if (!items.length) { tbody.innerHTML = '<tr><td colspan="5" style="color:var(--muted)">No items found.</td></tr>'; return; }
          tbody.innerHTML = items.map(i => `
            <tr>
              <td>${i.product_image ? `<img src="${i.product_image}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">` : '📦'}</td>
              <td>${i.product_name}</td>
              <td>₹${parseFloat(i.price).toFixed(2)}</td>
              <td>${i.quantity}</td>
              <td>₹${(i.price * i.quantity).toFixed(2)}</td>
            </tr>
          `).join('');
        });
    }
  } else {
    row.style.display = 'none';
  }
}
</script>
<?php layout_end(); ?>
