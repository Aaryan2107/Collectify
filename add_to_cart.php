<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/product_catalog.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: exp1.php');
    exit;
}

$productId = $_POST['product_id'] ?? '';
$redirect = $_POST['redirect'] ?? 'cart.php';

if (!isset($products[$productId])) {
    header('Location: ' . $redirect);
    exit;
}

$product = $products[$productId];
$userId = (int) $_SESSION['user_id'];

$check = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1');
$check->execute([$userId, $productId]);
$existing = $check->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $update = $pdo->prepare('UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?');
    $update->execute([$existing['id']]);
} else {
    $insert = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, 1)');
    $insert->execute([$userId, $productId, $product['name'], $product['image'], $product['price']]);
}

header('Location: cart.php');
exit;
