<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/product_catalog.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: exp1.php');
    exit;
}

$productId = $_POST['product_id'] ?? '';
if (!isset($products[$productId])) {
    header('Location: exp1.php');
    exit;
}

$product = $products[$productId];
$userId = (int) $_SESSION['user_id'];

$pdo->beginTransaction();
try {
    $orderInsert = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)');
    $orderInsert->execute([$userId, $product['price'], 'placed']);
    $orderId = (int) $pdo->lastInsertId();

    $itemInsert = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, 1)');
    $itemInsert->execute([$orderId, $productId, $product['name'], $product['image'], $product['price']]);

    $pdo->commit();
    header('Location: orders.php?ok=1');
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    header('Location: orders.php?ok=0');
    exit;
}
