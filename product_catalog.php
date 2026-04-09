<?php

function get_products_by_category_slug(PDO $pdo, string $slug): array
{
    $stmt = $pdo->prepare(
        'SELECT p.product_id, p.name, p.price, p.image, p.stock, p.is_active, c.slug AS category_slug, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE c.slug = ? AND p.is_active = 1
         ORDER BY p.sort_order, p.name'
    );
    $stmt->execute([$slug]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function get_product_by_product_id(PDO $pdo, string $productId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.product_id, p.name, p.price, p.image, p.stock, p.is_active, c.slug AS category_slug, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.product_id = ?
         LIMIT 1'
    );
    $stmt->execute([$productId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product ?: null;
}
