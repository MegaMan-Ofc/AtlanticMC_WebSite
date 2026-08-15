<?php

declare(strict_types=1);

$productCard = file_get_contents($root . '/templates/components/product-card.php');
$cartPanel = file_get_contents($root . '/templates/store/cart.php');
$mainJavaScript = file_get_contents($root . '/public_html/js/main.js');
$componentStyles = file_get_contents($root . '/public_html/css/components.css');
$searchTemplate = file_get_contents($root . '/templates/store/search.php');

$assert(
    is_string($productCard)
        && str_contains($productCard, 'data-quantity-stepper')
        && str_contains($productCard, 'data-quantity-decrease')
        && str_contains($productCard, 'data-quantity-increase')
        && str_contains($productCard, 'data-ajax-cart')
        && !str_contains($productCard, '<select class="field product-quantity"'),
    'Product cards use quantity steppers and keep AJAX add-to-cart behavior.'
);

$assert(
    is_string($componentStyles)
        && str_contains($componentStyles, 'height: 100%')
        && str_contains($componentStyles, '.quantity-stepper')
        && str_contains($componentStyles, 'margin-top: auto'),
    'Product cards keep purchase controls aligned at the bottom of equal-height cards.'
);

$assert(
    is_string($cartPanel)
        && str_contains($cartPanel, 'data-cart-auto-update')
        && str_contains($cartPanel, 'quantity-stepper--compact')
        && is_string($mainJavaScript)
        && str_contains($mainJavaScript, 'scheduleCartAutoUpdate')
        && str_contains($mainJavaScript, 'submitCartForm'),
    'Cart quantities update automatically through the shared AJAX cart endpoint.'
);

$assert(
    is_string($searchTemplate)
        && str_contains($searchTemplate, 'data-ajax-search')
        && str_contains($searchTemplate, 'data-search-link')
        && str_contains($searchTemplate, 'data-search-shell')
        && is_string($mainJavaScript)
        && str_contains($mainJavaScript, 'navigateSearch')
        && str_contains($mainJavaScript, 'history.pushState'),
    'Search submissions and filters progressively update results with AJAX and browser history support.'
);
