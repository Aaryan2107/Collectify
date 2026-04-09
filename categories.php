<?php
// admin/categories.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

$message = '';
$error   = '';

// ─── Handle POST actions ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD CATEGORY
    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 0);
        $slug  = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        if ($name === '') {
            $error = 'Category name is required.';
        } else {
            // Handle image upload
            $imagePath = '';
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $filename = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
                    $dest = __DIR__ . '/uploads/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $imagePath = 'uploads/' . $filename;
                    }
                }
            }
            try {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, image, sort_order) VALUES (?,?,?,?,?)');
                $stmt->execute([$name, $slug, $desc, $imagePath, $order]);
                $message = "Category \"$name\" added successfully!";
            } catch (PDOException $e) {
                $error = 'Category name or slug already exists.';
            }
        }
    }

    // RENAME / EDIT CATEGORY
    if ($action === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 0);
        $slug  = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        if ($name === '' || $id === 0) {
            $error = 'Invalid data.';
        } else {
            // Handle image upload on edit
            $imgUpdate = '';
            $imgParams = [];
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $filename = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
                    $dest = __DIR__ . '/uploads/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $imgUpdate  = ', image = ?';
                        $imgParams  = ['uploads/' . $filename];
                    }
                }
            }
            try {
                $params = array_merge([$name, $slug, $desc, $order], $imgParams, [$id]);
                $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, sort_order=? $imgUpdate WHERE id=?")->execute($params);
                $message = "Category updated successfully!";
            } catch (PDOException $e) {
                $error = 'That name or slug is already in use.';
            }
        }
    }

    // DELETE CATEGORY
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            $error = 'Cannot delete: category has products. Remove products first.';
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            $message = 'Category deleted.';
        }
    }

    header('Location: categories.php?msg=' . urlencode($message) . '&err=' . urlencode($error));
    exit();
}

if (isset($_GET['msg'])) $message = $_GET['msg'];
if (isset($_GET['err'])) $error   = $_GET['err'];

$categories = $pdo->query('SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.sort_order, c.name')->fetchAll(PDO::FETCH_ASSOC);

layout_head('Categories');
layout_sidebar('categories', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div><h1>Categories</h1><div class="breadcrumb">Manage product categories</div></div>
    <button class="btn btn-primary" onclick="openModal('addModal')">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
      Add Category
    </button>
  </div>
  <div class="content">

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h3>All Categories (<?= count($categories) ?>)</h3></div>
      <table>
        <thead>
          <tr><th>Image</th><th>Name</th><th>Slug</th><th>Description</th><th>Products</th><th>Order</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td>
            <?php if ($cat['image']): ?>
              <img src="<?= htmlspecialchars($cat['image']) ?>" class="product-thumb" alt="">
            <?php else: ?>
              <div class="product-thumb-placeholder">🏷️</div>
            <?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
          <td><code style="font-size:0.75rem;color:var(--accent)"><?= htmlspecialchars($cat['slug']) ?></code></td>
          <td style="color:var(--muted);max-width:200px;"><?= htmlspecialchars(substr($cat['description']??'',0,60)) ?><?= strlen($cat['description']??'')>60?'…':'' ?></td>
          <td><span class="badge badge-success"><?= $cat['product_count'] ?></span></td>
          <td><?= $cat['sort_order'] ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($cat)) ?>)">Edit</button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this category?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $cat['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">No categories yet. Add your first one!</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <h3>Add New Category</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label>Category Name *</label>
        <input type="text" name="name" placeholder="e.g. Hot Wheels" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" placeholder="Short description..."></textarea>
      </div>
      <div class="form-group">
        <label>Sort Order</label>
        <input type="number" name="sort_order" value="0" min="0">
      </div>
      <div class="form-group">
        <label>Category Image</label>
        <input type="file" name="image" accept="image/*">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Category</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h3>Edit Category</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-group">
        <label>Category Name *</label>
        <input type="text" name="name" id="edit_name" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" id="edit_description"></textarea>
      </div>
      <div class="form-group">
        <label>Sort Order</label>
        <input type="number" name="sort_order" id="edit_sort_order" min="0">
      </div>
      <div class="form-group">
        <label>Replace Image (optional)</label>
        <input type="file" name="image" accept="image/*">
        <img id="edit_img_preview" class="img-preview" src="" alt="" style="display:none;">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open'); }));
function openEdit(cat) {
  document.getElementById('edit_id').value          = cat.id;
  document.getElementById('edit_name').value        = cat.name;
  document.getElementById('edit_description').value = cat.description || '';
  document.getElementById('edit_sort_order').value  = cat.sort_order || 0;
  const prev = document.getElementById('edit_img_preview');
  if (cat.image) { prev.src = cat.image; prev.style.display = 'block'; }
  else { prev.style.display = 'none'; }
  openModal('editModal');
}
</script>
<?php layout_end(); ?>
