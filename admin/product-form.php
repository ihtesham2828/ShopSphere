<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$product = ['name'=>'', 'description'=>'', 'price'=>'', 'stock'=>'0', 'category_id'=>'', 'image_url'=>'', 'is_active'=>1];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { set_flash('error', 'Product not found.'); redirect('admin/products.php'); }
    $product = $found;
}
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $product['name'] = trim((string)($_POST['name'] ?? ''));
    $product['description'] = trim((string)($_POST['description'] ?? ''));
    $product['price'] = trim((string)($_POST['price'] ?? ''));
    $product['stock'] = trim((string)($_POST['stock'] ?? ''));
    $product['category_id'] = trim((string)($_POST['category_id'] ?? ''));
    $product['image_url'] = trim((string)($_POST['image_url'] ?? ''));
    $product['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $categoryId = filter_var($product['category_id'], FILTER_VALIDATE_INT);
    $price = filter_var($product['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($product['stock'], FILTER_VALIDATE_INT);
    if (mb_strlen($product['name']) < 2 || mb_strlen($product['name']) > 180) $errors[] = 'Product name must be between 2 and 180 characters.';
    if (mb_strlen($product['description']) < 5) $errors[] = 'Add a product description.';
    if ($price === false || $price < 0 || $price > 99999999) $errors[] = 'Enter a valid non-negative price.';
    if ($stock === false || $stock < 0 || $stock > 1000000) $errors[] = 'Enter a valid stock quantity.';
    if (!$categoryId) $errors[] = 'Choose a category.';
    if ($product['image_url'] !== '' && !filter_var($product['image_url'], FILTER_VALIDATE_URL)) $errors[] = 'Image URL must be a valid URL.';
    if (!$errors) {
        if ($id) {
            $stmt = db()->prepare('UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image_url=?, is_active=? WHERE id=?');
            $stmt->execute([$categoryId, $product['name'], $product['description'], $price, $stock, $product['image_url'], $product['is_active'], $id]);
            set_flash('success', 'Product updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO products (category_id, name, description, price, stock, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$categoryId, $product['name'], $product['description'], $price, $stock, $product['image_url'], $product['is_active']]);
            set_flash('success', 'Product created.');
        }
        redirect('admin/products.php');
    }
}
$pageTitle = $id ? 'Edit product' : 'Add product';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="container section narrow-section"><div class="section-heading"><div><span class="eyebrow">CATALOGUE</span><h1><?= $id ? 'Edit product' : 'Add a product' ?></h1></div><a class="text-link" href="<?= e(url('admin/products.php')) ?>">← All products</a></div>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="panel stack-form admin-product-form" method="post"><?= csrf_field() ?><label>Product name<input name="name" value="<?= e($product['name']) ?>" required maxlength="180"></label><label>Description<textarea name="description" rows="5" required><?= e($product['description']) ?></textarea></label><div class="form-grid"><label>Price (PKR)<input type="number" name="price" min="0" max="99999999" step="0.01" value="<?= e((string)$product['price']) ?>" required></label><label>Stock quantity<input type="number" name="stock" min="0" max="1000000" step="1" value="<?= e((string)$product['stock']) ?>" required></label></div><label>Category<select name="category_id" required><option value="">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$product['category_id'] === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label><label>Image URL <span class="muted">(optional)</span><input type="url" name="image_url" value="<?= e($product['image_url']) ?>" placeholder="https://example.com/image.jpg"></label><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" <?= (int)$product['is_active'] ? 'checked' : '' ?>> Visible in the public shop</label><button class="btn btn-primary btn-block" type="submit"><?= $id ? 'Save changes' : 'Create product' ?></button></form></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
