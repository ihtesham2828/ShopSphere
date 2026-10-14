<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function url(string $path = ''): string
{
    $base = APP_URL;
    if ($base === '') {
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        // Admin pages live one level deeper; their relative paths are passed explicitly.
        $base = rtrim($script, '/');
        if (str_ends_with($base, '/admin')) {
            $base = substr($base, 0, -6);
        }
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e($_SESSION['_csrf'] ?? '') . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['_csrf'] ?? '';
    $known = $_SESSION['_csrf'] ?? '';
    if (!is_string($submitted) || !is_string($known) || $known === '' || !hash_equals($known, $submitted)) {
        http_response_code(419);
        exit('Your form expired or could not be verified. Please go back, refresh the page, and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_logged_in() || (current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('403 — Admin access required.');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($flash) ? $flash : null;
}

function money(float|string|int $amount): string
{
    return 'Rs ' . number_format((float)$amount, 2);
}

function cart_count(): int
{
    $total = 0;
    foreach ($_SESSION['cart'] ?? [] as $quantity) {
        $total += (int)$quantity;
    }
    return $total;
}

function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($id) => $id > 0));
    if (!$ids) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $statement = db()->prepare("SELECT id, name, price, image_url, stock FROM products WHERE is_active = 1 AND id IN ($placeholders)");
    $statement->execute($ids);
    $products = $statement->fetchAll();
    $items = [];
    foreach ($products as $product) {
        $id = (int)$product['id'];
        $quantity = max(1, (int)($cart[$id] ?? 1));
        $items[] = [
            'product' => $product,
            'quantity' => min($quantity, max(1, (int)$product['stock'])),
            'line_total' => (float)$product['price'] * min($quantity, max(1, (int)$product['stock'])),
        ];
    }
    return $items;
}

function cart_subtotal(array $items): float
{
    return array_reduce($items, fn(float $sum, array $item): float => $sum + (float)$item['line_total'], 0.0);
}
