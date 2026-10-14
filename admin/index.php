<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$stats = [
    'products' => (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'customers' => (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'revenue' => (float)db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn(),
];
$recent = db()->query('SELECT o.id, o.full_name, o.total, o.status, o.created_at FROM orders o ORDER BY o.created_at DESC LIMIT 8')->fetchAll();
$pageTitle = 'Admin dashboard';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="container section"><div class="section-heading"><div><span class="eyebrow">STORE CONTROL</span><h1>Admin dashboard</h1><p class="muted">A quick overview of your ShopSphere demo store.</p></div><div class="admin-actions"><a class="btn btn-primary" href="<?= e(url('admin/product-form.php')) ?>">+ Add product</a></div></div>
<div class="stats-grid"><div class="stat-card"><span>Products</span><strong><?= $stats['products'] ?></strong><small>In catalogue</small></div><div class="stat-card"><span>Orders</span><strong><?= $stats['orders'] ?></strong><small>All time</small></div><div class="stat-card"><span>Customers</span><strong><?= $stats['customers'] ?></strong><small>Registered accounts</small></div><div class="stat-card"><span>Order value</span><strong><?= money($stats['revenue']) ?></strong><small>Excluding cancelled orders</small></div></div>
<div class="section-heading compact-heading"><div><h2>Recent orders</h2></div><a class="text-link" href="<?= e(url('admin/orders.php')) ?>">Manage orders →</a></div>
<?php if (!$recent): ?><div class="empty-state"><h3>No orders yet</h3><p>Placed orders will appear here.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach ($recent as $order): ?><tr><td>#<?= (int)$order['id'] ?></td><td><?= e($order['full_name']) ?></td><td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td><td><?= money($order['total']) ?></td><td><span class="status status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<div class="admin-link-grid"><a class="panel admin-link-card" href="<?= e(url('admin/products.php')) ?>"><span>▦</span><div><strong>Manage products</strong><p>Add, edit, hide, or update stock.</p></div><b>→</b></a><a class="panel admin-link-card" href="<?= e(url('admin/orders.php')) ?>"><span>☷</span><div><strong>Manage orders</strong><p>Review orders and update their status.</p></div><b>→</b></a></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
