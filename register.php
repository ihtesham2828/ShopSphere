<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) redirect('index.php');
$errors = [];
$name = trim((string)($_POST['name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Name must be between 2 and 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'An account with that email already exists.';
        else {
            $stmt = db()->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)db()->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'customer'];
            set_flash('success', 'Welcome to ShopSphere! Your account is ready.');
            redirect('index.php');
        }
    }
}
$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><div class="auth-symbol">S</div><span class="eyebrow">JOIN THE COMMUNITY</span><h1>Create your account</h1><p class="muted">Save your details and keep track of your orders.</p>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form method="post" class="stack-form"><?= csrf_field() ?><label>Full name<input name="name" value="<?= e($name) ?>" required maxlength="100" autocomplete="name"></label><label>Email address<input type="email" name="email" value="<?= e($email) ?>" required maxlength="190" autocomplete="email"></label><label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>Use at least 8 characters.</small></label><label>Confirm password<input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></label><button class="btn btn-primary btn-block" type="submit">Create account</button></form><p class="auth-switch">Already registered? <a href="<?= e(url('login.php')) ?>">Log in</a></p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
