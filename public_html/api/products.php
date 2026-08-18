<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_get();

$category = is_string($_GET['category'] ?? null) ? strtolower(trim($_GET['category'])) : '';

if (!store_category_exists($category, false)) {
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
        'price_cents' => product_effective_price_cents($product),
        'original_price_cents' => product_has_discount($product) ? (int) $product['price_cents'] : null,
        'discount_price_cents' => product_discount_price_cents($product),
        'discount_percentage' => product_discount_percentage($product),
        'currency' => $product['currency'],
        'metadata' => localized_product_metadata($product),
    ];
}, products_by_category($category));

json_response(['data' => $products, 'language' => current_language()]);
