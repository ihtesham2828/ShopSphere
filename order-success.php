<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$orderId, (int)current_user()['id']]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); $pageTitle = 'Order not found'; require __DIR__ . '/includes/header.php'; echo '<section class="container section"><div class="empty-state"><h1>Order not found</h1><a class="btn btn-primary" href="' . e(url('account.php')) . '">My orders</a></div></section>'; require __DIR__ . '/includes/footer.php'; exit; }
$itemStmt = db()->prepare('SELECT product_name, unit_price, quantity, line_total FROM order_items WHERE order_id = ?');
$itemStmt->execute([$orderId]);
$orderItems = $itemStmt->fetchAll();
$pageTitle = 'Order #' . $orderId;
require __DIR__ . '/includes/header.php';
?>
<section class="container section narrow-section"><div class="success-mark">✓</div><div class="centered"><span class="eyebrow">ORDER RECEIVED</span><h1>Thank you, <?= e($order['full_name']) ?>!</h1><p class="muted">Your demo order has been placed successfully.</p></div><div class="panel order-detail-panel"><div class="order-detail-top"><div><span class="muted small">Order number</span><h2>#<?= (int)$order['id'] ?></h2></div><span class="status status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></div><?php foreach ($orderItems as $item): ?><div class="summary-item"><div><strong><?= e($item['product_name']) ?></strong><span><?= (int)$item['quantity'] ?> × <?= money($item['unit_price']) ?></span></div><b><?= money($item['line_total']) ?></b></div><?php endforeach; ?><div class="summary-total"><span>Total</span><strong><?= money($order['total']) ?></strong></div><div class="delivery-summary"><strong>Delivery address</strong><p><?= e($order['address']) ?><br><?= e($order['city']) ?><br><?= e($order['phone']) ?></p><span class="muted small">Payment: Cash on delivery (demo)</span></div></div><div class="centered button-row"><a class="btn btn-primary" href="<?= e(url('account.php')) ?>">View my orders</a><a class="btn btn-light" href="<?= e(url('index.php')) ?>">Continue shopping</a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
