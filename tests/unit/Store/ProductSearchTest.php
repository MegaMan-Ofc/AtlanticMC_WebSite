<?php

declare(strict_types=1);

$sampleProduct = [
    'slug' => 'sea-hunter',
    'category' => 'ranks',
    'category_name' => 'Ranks',
    'name' => 'Sea Hunter',
    'description' => 'Advanced rank for active Atlantic players.',
    'metadata' => json_encode(['features' => ['Extra commands', 'Higher limits']]),
];

$assert(
    product_search_normalize('Corações Épicos') === 'coracoes epicos',
    'Product search normalization ignores Portuguese accents and punctuation.'
);

$assert(
    product_search_score($sampleProduct, 'Sea Hunter') > product_search_score($sampleProduct, 'rank'),
    'Exact product names rank above generic category matches.'
);

$assert(
    product_search_score($sampleProduct, 'sea huntr') > 0,
    'Product search tolerates a small typo in meaningful product-name tokens.'
);

$assert(
    product_search_score($sampleProduct, 'extra commands') > 0,
    'Product search considers localized product metadata and features.'
);

$searchPage = file_get_contents($root . '/public_html/search.php');
$searchTemplate = file_get_contents($root . '/templates/store/search.php');
$searchAlgorithm = file_get_contents($root . '/app/Store/Search/product_search.php');
$homePage = file_get_contents($root . '/public_html/index.php');

$assert(
    is_string($searchPage)
        && is_string($searchTemplate)
        && str_contains($searchTemplate, 'search-filter-chip')
        && str_contains($searchTemplate, 'product-card.php')
        && is_string($searchAlgorithm)
        && str_contains($searchAlgorithm, 'product_search_score')
        && is_string($homePage)
        && str_contains($homePage, "route_url('search')")
        && str_contains($homePage, 'category-card--search'),
    'The storefront exposes a dedicated search route, filter controls, shared product cards, and a Shop search tile.'
);
