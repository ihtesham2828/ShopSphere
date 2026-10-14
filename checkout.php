<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$items = cart_items();
if (!$items) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}
$errors = [];
$name = trim((string)($_POST['full_name'] ?? current_user()['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$city = trim((string)($_POST['city'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Enter your full name.';
    if (!preg_match('/^[0-9+\-\s()]{7,25}$/', $phone)) $errors[] = 'Enter a valid phone number.';
    if (mb_strlen($address) < 8 || mb_strlen($address) > 500) $errors[] = 'Enter a complete delivery address.';
    if (mb_strlen($city) < 2 || mb_strlen($city) > 100) $errors[] = 'Enter your city.';

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $freshItems = [];
            $total = 0.0;
            foreach ($_SESSION['cart'] ?? [] as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity;
                if ($productId < 1 || $quantity < 1) continue;
                $stmt = $pdo->prepare('SELECT id, name, price, stock, is_active FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product || !(int)$product['is_active'] || (int)$product['stock'] < $quantity) {
                    throw new RuntimeException('A product is no longer available in the selected quantity. Please review your cart.');
                }
                $line = (float)$product['price'] * $quantity;
                $freshItems[] = ['product' => $product, 'quantity' => $quantity, 'line_total' => $line];
                $total += $line;
            }
            if (!$freshItems) throw new RuntimeException('Your cart is empty.');
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, full_name, phone, address, city, total, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'cash_on_delivery')");
            $orderStmt->execute([(int)current_user()['id'], $name, $phone, $address, $city, $total]);
            $orderId = (int)$pdo->lastInsertId();
            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($freshItems as $item) {
                $p = $item['product'];
                $itemStmt->execute([$orderId, (int)$p['id'], $p['name'], $p['price'], $item['quantity'], $item['line_total']]);
                $stockStmt->execute([$item['quantity'], (int)$p['id'], $item['quantity']]);
                if ($stockStmt->rowCount() !== 1) throw new RuntimeException('Stock changed during checkout. Please try again.');
            }
            $pdo->commit();
            $_SESSION['cart'] = [];
            redirect('order-success.php?id=' . $orderId);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'We could not place your order. Please try again.';
        }
    }
}
$items = cart_items();
$subtotal = cart_subtotal($items);
$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<section class="container section">
  <div class="section-heading"><div><span class="eyebrow">ALMOST THERE</span><h1>Checkout</h1></div><a class="text-link" href="<?= e(url('cart.php')) ?>">← Back to cart</a></div>
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
  <div class="checkout-layout">
    <form class="panel checkout-form" method="post"><?= csrf_field() ?><h2>Delivery details</h2>
      <label>Full name<input name="full_name" value="<?= e($name) ?>" required maxlength="120" autocomplete="name"></label>
      <div class="form-grid"><label>Phone number<input name="phone" value="<?= e($phone) ?>" required maxlength="25" autocomplete="tel" placeholder="+92 300 1234567"></label><label>City<input name="city" value="<?= e($city) ?>" required maxlength="100" autocomplete="address-level2"></label></div>
      <label>Full address<textarea name="address" rows="4" required maxlength="500" autocomplete="street-address"><?= e($address) ?></textarea></label>
      <div class="payment-choice"><span class="payment-icon">₨</span><div><strong>Cash on delivery</strong><p>Demo payment method — pay when the order arrives.</p></div><span class="checkmark">✓</span></div>
      <button class="btn btn-primary btn-block" type="submit">Place order · <?= money($subtotal) ?></button>
      <p class="fine-print">This demo does not process online payments.</p>
    </form>
    <aside class="panel order-summary"><h2>Order summary</h2><?php foreach ($items as $item): ?><div class="summary-item"><img src="<?= e($item['product']['image_url']) ?>" alt=""><div><strong><?= e($item['product']['name']) ?></strong><span>Qty <?= (int)$item['quantity'] ?></span></div><b><?= money($item['line_total']) ?></b></div><?php endforeach; ?><div class="summary-total"><span>Total</span><strong><?= money($subtotal) ?></strong></div><p class="muted small">Shipping is not included in this portfolio demo.</p></aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
