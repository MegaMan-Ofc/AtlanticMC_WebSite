<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_get();

$category = is_string($_GET['category'] ?? null) ? strtolower(trim($_GET['category'])) : '';

if (!in_array($category, STORE_CATEGORIES, true)) {
    json_response(['error' => 'Invalid category.'], 422);
}

$products = array_map(static function (array $product): array {
    return [
        'id' => (int) $product['id'],
        'slug' => $product['slug'],
        'category' => $product['category'],
        'name' => $product['name'],
        'description' => $product['description'],
        'image' => url($product['image']),
        'price_cents' => (int) $product['price_cents'],
        'currency' => $product['currency'],
        'metadata' => product_metadata($product),
    ];
}, products_by_category($category));

json_response(['data' => $products]);
