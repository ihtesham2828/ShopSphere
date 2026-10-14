<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$allowedStatuses = ['pending', 'processing', 'shipped', 'cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT) ?: 0;
    $status = (string)($_POST['status'] ?? '');
    if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        set_flash('success', 'Order status updated.');
    } else {
        set_flash('error', 'Invalid order status.');
    }
    redirect('admin/orders.php');
}
$orders = db()->query('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
$pageTitle = 'Manage orders';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="container section"><div class="section-heading"><div><span class="eyebrow">FULFILMENT</span><h1>Manage orders</h1><p class="muted">Review orders and update their progress.</p></div></div>
<?php if (!$orders): ?><div class="empty-state"><h2>No orders yet</h2><p>Orders will appear here after checkout.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Placed</th><th>Total</th><th>Status</th><th>Update status</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td>#<?= (int)$order['id'] ?></td><td><strong><?= e($order['full_name']) ?></strong><small class="table-subtext"><?= e($order['email']) ?></small></td><td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td><td><?= money($order['total']) ?></td><td><span class="status status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td><td><form class="status-form" method="post"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><select name="status" aria-label="Status for order <?= (int)$order['id'] ?>"><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select><button class="btn btn-small btn-dark" type="submit">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
