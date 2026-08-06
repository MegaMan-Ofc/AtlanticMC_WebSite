<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_get();

$category = is_string($_GET['category'] ?? null) ? strtolower(trim($_GET['category'])) : '';

if (!in_array($category, STORE_CATEGORIES, true)) {
    json_response(['error' => t('validation.invalid_category')], 422);
}

$products = array_map(static function (array $product): array {
    $localizedProduct = localized_product($product);

    return [
        'id' => (int) $product['id'],
        'slug' => $product['slug'],
        'category' => $product['category'],
        'category_label' => localized_category((string) $product['category']),
        'name' => $localizedProduct['name'],
        'description' => $localizedProduct['description'],
        'image' => url($product['image']),
        'price_cents' => (int) $product['price_cents'],
        'currency' => $product['currency'],
        'metadata' => localized_product_metadata($product),
    ];
}, products_by_category($category));

json_response(['data' => $products, 'language' => current_language()]);
