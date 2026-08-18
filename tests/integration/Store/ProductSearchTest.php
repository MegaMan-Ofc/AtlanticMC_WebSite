<?php

declare(strict_types=1);

$seaHunterResults = product_search_results('sea huntr');
$assert(
    $seaHunterResults !== [] && (string) $seaHunterResults[0]['slug'] === 'sea-hunter',
    'Database product search ranks the intended product first even with a small typo.'
);

$rubiResults = product_search_results('rubis', 'rubis');
$assert(
    count($rubiResults) >= 3
        && count(array_unique(array_column($rubiResults, 'category_slug'))) === 1
        && (string) $rubiResults[0]['category_slug'] === 'rubis',
    'Product search combines textual relevance with a category filter.'
);

$discountResults = product_search_results('', '', true, 'price-asc');
$assert(
    $discountResults !== []
        && array_reduce($discountResults, static fn (bool $valid, array $product): bool => $valid && product_has_discount($product), true),
    'Discount filtering only returns products with a valid promotional price.'
);

$prices = array_map('product_effective_price_cents', $discountResults);
$sortedPrices = $prices;
sort($sortedPrices, SORT_NUMERIC);
$assert($prices === $sortedPrices, 'Price ascending search sorting uses the effective promotional price.');
