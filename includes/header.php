<?php
$pageTitle = $pageTitle ?? APP_NAME;
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
  <meta name="description" content="ShopSphere demo e-commerce store built with PHP and MySQL.">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= e(url('index.php')) ?>"><span class="brand-mark">S</span> ShopSphere</a>
    <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
    <nav class="main-nav">
      <a href="<?= e(url('index.php')) ?>">Shop</a>
      <a href="<?= e(url('cart.php')) ?>">Cart <span class="cart-count"><?= cart_count() ?></span></a>
      <?php if (is_logged_in()): ?>
        <a href="<?= e(url('account.php')) ?>">My orders</a>
        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
          <a href="<?= e(url('admin/index.php')) ?>">Admin</a>
        <?php endif; ?>
        <form class="nav-logout" method="post" action="<?= e(url('logout.php')) ?>">
          <?= csrf_field() ?><button class="link-button" type="submit">Log out</button>
        </form>
      <?php else: ?>
        <a href="<?= e(url('login.php')) ?>">Log in</a>
        <a class="nav-cta" href="<?= e(url('register.php')) ?>">Create account</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>
<?php if ($flash): ?>
  <div class="container"><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div></div>
<?php endif; ?>
