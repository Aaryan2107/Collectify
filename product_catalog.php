<?php

function normalize_catalog_slug(string $slug): string
{
    return strtolower(str_replace(['-', ' '], '', trim($slug)));
}

function catalog_page_for_slug(string $slug): ?string
{
    return match (normalize_catalog_slug($slug)) {
        'hotwheels' => 'hotwheels.php',
        'minigt' => 'minigt.php',
        'lego' => 'lego.php',
        default => null,
    };
}

function default_category_image(string $slug): string
{
    return match (normalize_catalog_slug($slug)) {
        'hotwheels' => 'images/hotwheels.webp',
        'minigt' => 'images/minigt.webp',
        'lego' => 'images/lego.webp',
        default => 'images/hotwheels.webp',
    };
}

function get_home_categories(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT c.id, c.name, c.slug, c.description, c.image, c.sort_order, COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
         GROUP BY c.id, c.name, c.slug, c.description, c.image, c.sort_order
         ORDER BY c.sort_order, c.name'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function get_products_by_category_slug(PDO $pdo, string $slug): array
{
    $normalizedSlug = normalize_catalog_slug($slug);
    $stmt = $pdo->prepare(
        'SELECT p.product_id, p.name, p.price, p.image, p.stock, p.is_active, c.slug AS category_slug, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE REPLACE(REPLACE(LOWER(c.slug), "-", ""), " ", "") = ? AND p.is_active = 1
         ORDER BY p.sort_order, p.name'
    );
    $stmt->execute([$normalizedSlug]);

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
