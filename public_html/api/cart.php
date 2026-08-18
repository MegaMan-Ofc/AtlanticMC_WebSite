<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_get();

$summary = cart_summary();
$items = array_map(static function (array $item): array {
    $product = localized_product($item['product']);

    return [
        'product_id' => (int) $item['product']['id'],
        'name' => $product['name'],
        'quantity' => (int) $item['quantity'],
        'unit_price_cents' => product_effective_price_cents($item['product']),
        'line_total_cents' => (int) $item['line_total_cents'],
    ];
}, $summary['items']);

json_response(['data' => [
    'items' => $items,
    'item_count' => $summary['item_count'],
    'subtotal_cents' => $summary['subtotal_cents'],
    'discount_cents' => $summary['discount_cents'],
    'total_cents' => $summary['total_cents'],
    'currency' => $summary['currency'],
    'language' => current_language(),
]]);
