<?php
// admin/add_product.php  — handles both ADD and EDIT
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';
require_once '_layout.php';

$message  = '';
$error    = '';
$editMode = false;
$product  = [
    'id'=>0,'category_id'=>'','product_id'=>'','name'=>'',
    'description'=>'','price'=>'','stock'=>0,'sort_order'=>0,
    'image'=>'','is_active'=>1
];

// Load existing product for edit
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { $product = $row; $editMode = true; }
    else { header('Location: products.php'); exit(); }
}

// ─── Handle POST ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id     = (int)($_POST['id'] ?? 0);

    $catId   = (int)($_POST['category_id'] ?? 0);
    $pid     = trim($_POST['product_id'] ?? '');
    $name    = trim($_POST['name'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $price   = (float)($_POST['price'] ?? 0);
    $stock   = (int)($_POST['stock'] ?? 0);
    $order   = (int)($_POST['sort_order'] ?? 0);
    $active  = isset($_POST['is_active']) ? 1 : 0;

    // Basic validation
    if ($catId === 0 || $pid === '' || $name === '' || $price <= 0) {
        $error = 'Please fill in all required fields (category, product ID, name, price).';
    } else {
        // Image upload
        $imagePath = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Invalid image format. Use JPG, PNG, GIF, or WebP.';
            } else {
                $filename = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
              $error = 'Upload folder is missing and could not be created.';
            }

            $dest = $uploadDir . '/' . $filename;
            if (!$error && move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    // Delete old image if editing
                    if ($imagePath && file_exists(__DIR__ . '/' . $imagePath)) {
                        @unlink(__DIR__ . '/' . $imagePath);
                    }
                    $imagePath = 'uploads/' . $filename;
                } else {
              if (!$error) {
                $error = 'Failed to upload image. Check folder permissions for uploads/.';
              }
                }
            }
        }

        if (!$error) {
            try {
                if ($action === 'edit' && $id > 0) {
                    $pdo->prepare('UPDATE products SET category_id=?,product_id=?,name=?,description=?,price=?,stock=?,sort_order=?,is_active=?,image=? WHERE id=?')
                        ->execute([$catId,$pid,$name,$desc,$price,$stock,$order,$active,$imagePath,$id]);
                    header('Location: products.php?msg=' . urlencode("\"$name\" updated successfully!"));
                } else {
                    $pdo->prepare('INSERT INTO products (category_id,product_id,name,description,price,stock,sort_order,is_active,image) VALUES (?,?,?,?,?,?,?,?,?)')
                        ->execute([$catId,$pid,$name,$desc,$price,$stock,$order,$active,$imagePath]);
                    header('Location: products.php?msg=' . urlencode("\"$name\" added successfully!"));
                }
                exit();
            } catch (PDOException $e) {
                $error = 'Product ID already exists or database error.';
            }
        }
    }

    // Re-populate form on error
    $product = array_merge($product, [
        'id'=>$id,'category_id'=>$catId,'product_id'=>$pid,'name'=>$name,
        'description'=>$desc,'price'=>$price,'stock'=>$stock,'sort_order'=>$order,
        'is_active'=>$active,'image'=>$_POST['existing_image']??''
    ]);
    $editMode = ($action === 'edit');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $editMode ? 'Edit Product' : 'Add Product';
layout_head($pageTitle);
layout_sidebar($editMode ? 'products' : 'add_product', $admin_username);
?>
<div class="main">
  <div class="topbar">
    <div>
      <h1><?= $pageTitle ?></h1>
      <div class="breadcrumb"><a href="products.php" style="color:var(--accent);text-decoration:none;">Products</a> / <?= $pageTitle ?></div>
    </div>
    <a href="products.php" class="btn btn-ghost">← Back to Products</a>
  </div>
  <div class="content">

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
      <input type="hidden" name="id" value="<?= $product['id'] ?>">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image']) ?>">

      <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

        <!-- LEFT COLUMN -->
        <div>
          <div class="form-card">
            <h3>Product Information</h3>
            <div class="form-grid">
              <div class="form-group full">
                <label>Product Name *</label>
                <input type="text" name="name" placeholder="e.g. Hot Wheels RLC Exclusive" required
                       value="<?= htmlspecialchars($product['name']) ?>">
              </div>
              <div class="form-group">
                <label>Product ID * <small style="color:var(--muted);font-weight:400;">(used in cart/orders)</small></label>
                <input type="text" name="product_id" placeholder="e.g. hw_001" required
                       value="<?= htmlspecialchars($product['product_id']) ?>"
                       <?= $editMode ? 'readonly style="opacity:0.6"' : '' ?>>
              </div>
              <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                  <option value="">Select category…</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group full">
                <label>Description</label>
                <textarea name="description" placeholder="Describe this product..."><?= htmlspecialchars($product['description']) ?></textarea>
              </div>
            </div>
          </div>

          <div class="form-card">
            <h3>Pricing & Inventory</h3>
            <div class="form-grid">
              <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" name="price" step="0.01" min="0" required placeholder="0.00"
                       value="<?= htmlspecialchars($product['price']) ?>">
              </div>
              <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" min="0" placeholder="0"
                       value="<?= htmlspecialchars($product['stock']) ?>">
              </div>
              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" min="0" value="<?= htmlspecialchars($product['sort_order']) ?>">
              </div>
              <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:24px;">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?> style="width:auto;accent-color:var(--accent);">
                <label for="is_active" style="text-transform:none;font-size:0.9rem;cursor:pointer;">Visible to customers</label>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Image -->
        <div>
          <div class="form-card">
            <h3>Product Image</h3>
            <div id="drop-zone" class="upload-zone" onclick="document.getElementById('imgFile').click()">
              <?php if ($product['image']): ?>
                <img id="img-preview" src="<?= htmlspecialchars($product['image']) ?>" alt="Preview" style="max-width:100%;border-radius:8px;display:block;margin:0 auto;">
              <?php else: ?>
                <div id="img-placeholder">
                  <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.3)" style="display:block;margin:0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <p>Click to upload image<br><small>JPG, PNG, WebP — max 5MB</small></p>
                </div>
                <img id="img-preview" src="" alt="" style="display:none;max-width:100%;border-radius:8px;">
              <?php endif; ?>
            </div>
            <input type="file" id="imgFile" name="image" accept="image/*" style="display:none" onchange="previewImg(this)">
            <?php if ($product['image']): ?>
            <p style="font-size:0.75rem;color:var(--muted);margin-top:8px;text-align:center;">Click image to replace</p>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:0.95rem;">
          <?= $editMode ? '💾 Save Changes' : '✚ Add Product' ?>
        </button>
        <a href="products.php" class="btn btn-ghost" style="padding:12px 20px;">Cancel</a>
      </div>
    </form>

  </div>
</div>

<script>
function previewImg(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById('img-preview');
    const plac = document.getElementById('img-placeholder');
    prev.src = e.target.result;
    prev.style.display = 'block';
    if (plac) plac.style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}
// Drag-and-drop
const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='var(--accent)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor=''; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor='';
  const file = e.dataTransfer.files[0];
  if (file) {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('imgFile').files = dt.files;
    previewImg(document.getElementById('imgFile'));
  }
});
</script>
<?php layout_end(); ?>
