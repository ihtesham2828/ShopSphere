<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$stmt = db()->prepare('SELECT id, total, status, payment_method, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([(int)current_user()['id']]);
$orders = $stmt->fetchAll();
$pageTitle = 'My orders';
require __DIR__ . '/includes/header.php';
?>
<section class="container section narrow-section"><div class="section-heading"><div><span class="eyebrow">YOUR ACCOUNT</span><h1>My orders</h1><p class="muted">Welcome, <?= e(current_user()['name']) ?>.</p></div></div>
<?php if (!$orders): ?><div class="empty-state"><div class="empty-icon">⌁</div><h2>No orders yet</h2><p>Your placed orders will appear here.</p><a class="btn btn-primary" href="<?= e(url('index.php')) ?>">Start shopping</a></div>
<?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td>#<?= (int)$order['id'] ?></td><td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td><td><?= money($order['total']) ?></td><td><span class="status status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td><td><a class="text-link" href="<?= e(url('order-success.php?id=' . (int)$order['id'])) ?>">Details</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
