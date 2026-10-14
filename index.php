<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = trim((string)($_GET['q'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($categoryId > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY p.created_at DESC, p.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Discover everyday favourites';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="eyebrow">THE EVERYDAY EDIT</span>
      <h1>Good finds.<br><span>Better living.</span></h1>
      <p>Thoughtfully picked essentials for your everyday life. Explore the collection and find your next favourite.</p>
      <a class="btn btn-primary" href="#products">Explore collection <span aria-hidden="true">→</span></a>
      <div class="hero-trust"><span>✓ Curated products</span><span>✓ Easy checkout</span></div>
    </div>
    <div class="hero-art" role="img" aria-label="Lifestyle product collection">
      <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
      <div class="hero-product hero-product-main"><span>✦</span><small>EVERYDAY<br>ESSENTIALS</small></div>
      <div class="hero-product hero-product-small">✳</div>
      <div class="hero-note"><strong>Made for your routine</strong><span>Simple picks, useful things.</span></div>
    </div>
  </div>
</section>
<section class="section container" id="products">
  <div class="section-heading">
    <div><span class="eyebrow">SHOP THE COLLECTION</span><h2>Find your next favourite</h2></div>
    <span class="muted"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></span>
  </div>
  <form class="filter-bar" method="get" action="<?= e(url('index.php')) ?>">
    <label class="search-field"><span class="sr-only">Search products</span><input type="search" name="q" placeholder="Search products..." value="<?= e($search) ?>"></label>
    <label><span class="sr-only">Category</span><select name="category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
    <button class="btn btn-dark" type="submit">Filter</button>
    <?php if ($search !== '' || $categoryId > 0): ?><a class="btn btn-light" href="<?= e(url('index.php')) ?>">Clear</a><?php endif; ?>
  </form>
  <?php if (!$products): ?>
    <div class="empty-state"><div class="empty-icon">⌕</div><h3>No products found</h3><p>Try another search or category.</p></div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <article class="product-card">
          <a class="product-image" href="<?= e(url('product.php?id=' . (int)$product['id'])) ?>">
            <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" loading="lazy" onerror="this.src='https://placehold.co/600x450/f1eee8/252525?text=ShopSphere'">
            <?php if ((int)$product['stock'] < 5): ?><span class="stock-badge">Almost gone</span><?php endif; ?>
          </a>
          <div class="product-info"><span class="product-category"><?= e($product['category_name'] ?? 'Collection') ?></span>
            <h3><a href="<?= e(url('product.php?id=' . (int)$product['id'])) ?>"><?= e($product['name']) ?></a></h3>
            <div class="product-bottom"><strong><?= money($product['price']) ?></strong><a class="round-arrow" aria-label="View <?= e($product['name']) ?>" href="<?= e(url('product.php?id=' . (int)$product['id'])) ?>">↗</a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<section class="benefits"><div class="container benefits-grid"><div><span>01</span><strong>Useful by design</strong><p>Everyday products with purpose.</p></div><div><span>02</span><strong>Simple checkout</strong><p>A clear, easy ordering experience.</p></div><div><span>03</span><strong>Made to explore</strong><p>Find something new for your routine.</p></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
