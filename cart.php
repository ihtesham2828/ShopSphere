<?php
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'remove' && $id > 0) {
        unset($_SESSION['cart'][$id]);
        set_flash('success', 'Item removed from cart.');
        redirect('cart.php');
    }
    if ($action === 'update') {
        $quantities = $_POST['quantity'] ?? [];
        if (is_array($quantities)) {
            foreach ($quantities as $productId => $quantity) {
                $productId = filter_var($productId, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if (!$productId || !isset($_SESSION['cart'][$productId])) continue;
                if (!$quantity || $quantity < 1) { unset($_SESSION['cart'][$productId]); continue; }
                $stmt = db()->prepare('SELECT stock FROM products WHERE id = ? AND is_active = 1');
                $stmt->execute([$productId]);
                $stock = $stmt->fetchColumn();
                if ($stock === false || (int)$stock < 1) unset($_SESSION['cart'][$productId]);
                else $_SESSION['cart'][$productId] = min(20, (int)$stock, $quantity);
            }
        }
        set_flash('success', 'Cart updated.');
        redirect('cart.php');
    }
}
$items = cart_items();
$subtotal = cart_subtotal($items);
$pageTitle = 'Your cart';
require __DIR__ . '/includes/header.php';
?>
<section class="container section narrow-section">
  <div class="section-heading"><div><span class="eyebrow">YOUR SELECTION</span><h1>Your cart</h1></div><a class="text-link" href="<?= e(url('index.php')) ?>">← Continue shopping</a></div>
  <?php if (!$items): ?>
    <div class="empty-state"><div class="empty-icon">＋</div><h2>Your cart is waiting</h2><p>Explore the collection and add something you like.</p><a class="btn btn-primary" href="<?= e(url('index.php')) ?>">Browse products</a></div>
  <?php else: ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="update">
      <div class="cart-list">
      <?php foreach ($items as $item): $p = $item['product']; ?>
        <div class="cart-row">
          <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" onerror="this.src='https://placehold.co/180x180/f1eee8/252525?text=Product'">
          <div class="cart-item-info"><a href="<?= e(url('product.php?id=' . (int)$p['id'])) ?>"><strong><?= e($p['name']) ?></strong></a><span><?= money($p['price']) ?> each</span><button class="text-button remove-button" type="submit" form="remove-<?= (int)$p['id'] ?>">Remove</button></div>
          <label class="quantity-label">Qty<input type="number" name="quantity[<?= (int)$p['id'] ?>]" min="1" max="<?= max(1, (int)$p['stock']) ?>" value="<?= (int)$item['quantity'] ?>"></label>
          <strong class="line-total"><?= money($item['line_total']) ?></strong>
        </div>
      <?php endforeach; ?>
      </div>
      <div class="cart-actions"><button class="btn btn-light" type="submit">Update cart</button><div class="cart-summary"><span>Subtotal</span><strong><?= money($subtotal) ?></strong><small>Shipping and taxes are not calculated in this demo.</small><a class="btn btn-primary btn-block" href="<?= e(url('checkout.php')) ?>">Continue to checkout</a></div></div>
    </form>
    <?php foreach ($items as $item): ?><form id="remove-<?= (int)$item['product']['id'] ?>" method="post" class="hidden-form"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$item['product']['id'] ?>"></form><?php endforeach; ?>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
