<?php
// admin/get_order_items.php
require_once __DIR__ . '/config.php';
require_once 'auth_check.php';

header('Content-Type: application/json');
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
