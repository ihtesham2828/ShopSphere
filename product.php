<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ? AND p.is_active = 1 LIMIT 1');
$stmt->execute([$id ?: 0]);
$product = $stmt->fetch();
if (!$product) { http_response_code(404); $pageTitle = 'Product not found'; require __DIR__ . '/includes/header.php'; echo '<section class="container section"><div class="empty-state"><h1>Product not found</h1><a class="btn btn-primary" href="' . e(url('index.php')) . '">Back to shop</a></div></section>'; require __DIR__ . '/includes/footer.php'; exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $quantity = $quantity ?: 1;
    if ($quantity < 1 || $quantity > 20) $errors[] = 'Choose a quantity between 1 and 20.';
    if ((int)$product['stock'] < 1) $errors[] = 'This product is out of stock.';
    elseif ($quantity > (int)$product['stock']) $errors[] = 'Only ' . (int)$product['stock'] . ' item(s) are available.';
    if (!$errors) {
        $cart = $_SESSION['cart'] ?? [];
        $newQuantity = (int)($cart[$id] ?? 0) + $quantity;
        if ($newQuantity > (int)$product['stock']) $newQuantity = (int)$product['stock'];
        $cart[$id] = $newQuantity;
        $_SESSION['cart'] = $cart;
        set_flash('success', 'Added to your cart.');
        redirect('cart.php');
    }
}
$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<section class="container section">
  <div class="breadcrumbs"><a href="<?= e(url('index.php')) ?>">Shop</a><span>/</span><span><?= e($product['name']) ?></span></div>
  <div class="product-detail">
    <div class="detail-image"><img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" onerror="this.src='https://placehold.co/800x700/f1eee8/252525?text=ShopSphere'"></div>
    <div class="detail-copy"><span class="eyebrow"><?= e($product['category_name'] ?? 'COLLECTION') ?></span><h1><?= e($product['name']) ?></h1><p class="detail-price"><?= money($product['price']) ?></p><p class="detail-description"><?= nl2br(e($product['description'])) ?></p>
      <p class="stock-text"><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' in stock' : 'Currently out of stock' ?></p>
      <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
      <form method="post" class="add-cart-form"><?= csrf_field() ?><label for="quantity">Quantity</label><input id="quantity" type="number" name="quantity" min="1" max="<?= max(1, min(20, (int)$product['stock'])) ?>" value="1" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>><button class="btn btn-primary" type="submit" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button></form>
      <div class="detail-perks"><span>✓ Secure account login</span><span>✓ Cash on delivery demo checkout</span></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
