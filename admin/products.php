<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $action = (string)($_POST['action'] ?? '');
    if ($id > 0 && $action === 'toggle') {
        $stmt = db()->prepare('UPDATE products SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Product visibility updated.');
    }
    redirect('admin/products.php');
}
$products = db()->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
$pageTitle = 'Manage products';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="container section"><div class="section-heading"><div><span class="eyebrow">CATALOGUE</span><h1>Manage products</h1><p class="muted">Create and maintain your product catalogue.</p></div><a class="btn btn-primary" href="<?= e(url('admin/product-form.php')) ?>">+ Add product</a></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Visibility</th><th>Actions</th></tr></thead><tbody><?php foreach ($products as $p): ?><tr><td><div class="table-product"><img src="<?= e($p['image_url']) ?>" alt=""><strong><?= e($p['name']) ?></strong></div></td><td><?= e($p['category_name'] ?? '—') ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?></td><td><span class="status <?= (int)$p['is_active'] ? 'status-processing' : 'status-cancelled' ?>"><?= (int)$p['is_active'] ? 'Active' : 'Hidden' ?></span></td><td><div class="table-actions"><a class="text-link" href="<?= e(url('admin/product-form.php?id=' . (int)$p['id'])) ?>">Edit</a><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="text-button" type="submit"><?= (int)$p['is_active'] ? 'Hide' : 'Show' ?></button></form></div></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
