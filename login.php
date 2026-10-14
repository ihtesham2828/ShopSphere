<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) redirect('index.php');
$errors = [];
$email = strtolower(trim((string)($_POST['email'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($password === '') $errors[] = 'Enter your password.';
    if (!$errors) {
        $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
            set_flash('success', 'You are now logged in.');
            redirect('index.php');
        }
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><div class="auth-symbol">S</div><span class="eyebrow">WELCOME BACK</span><h1>Log in</h1><p class="muted">Good to see you again.</p>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form method="post" class="stack-form"><?= csrf_field() ?><label>Email address<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-primary btn-block" type="submit">Log in</button></form><p class="auth-switch">New here? <a href="<?= e(url('register.php')) ?>">Create an account</a></p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
