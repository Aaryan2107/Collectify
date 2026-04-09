<?php
require_once __DIR__ . '/auth.php';
require_login();

$userId = (int) $_SESSION['user_id'];

$itemsStmt = $pdo->prepare('SELECT product_id, product_name, product_image, price, quantity FROM cart_items WHERE user_id = ?');
$itemsStmt->execute([$userId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
}

$pdo->beginTransaction();
try {
    $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)');
    $orderStmt->execute([$userId, $total, 'placed']);
    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $itemStmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['product_image'],
            $item['price'],
            $item['quantity']
        ]);
    }

    $clearStmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $clearStmt->execute([$userId]);

    $pdo->commit();
    header('Location: orders.php?ok=1');
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    header('Location: cart.php');
    exit;
}
