<?php

declare(strict_types=1);

$assert(
    public_route_name_from_request_uri('/') === 'home'
        && public_route_name_from_request_uri('/admin') === 'admin'
        && public_route_name_from_request_uri('/purchase-policy?from=footer') === 'purchase-policy'
        && public_route_name_from_request_uri('/faq') === 'faq'
        && public_route_name_from_request_uri('/search?q=sea') === 'search'
        && public_route_name_from_request_uri('/does-not-exist') === null,
    'Clean request paths resolve through the shared public route table.'
);

$assert(
    public_category_slug_from_request_uri('/custom-kits') === 'custom-kits'
        && public_category_slug_from_request_uri('/cart') === null
        && category_path('custom-kits') === 'custom-kits'
        && safe_return_path('/custom-kits?source=store', '') === 'custom-kits?source=store',
    'Dynamic category slugs resolve to clean public paths without colliding with reserved routes.'
);
$throws(
    static fn () => validate_category_slug('cart'),
    'Category slugs cannot use reserved public routes.'
);

$assert(
    array_keys(public_routes()) === [
        'home',
        'ranks',
        'rubis',
        'keys',
        'boosters',
        'search',
        'cart',
        'checkout',
        'login',
        'success',
        'privacy',
        'terms',
        'purchase-policy',
        'rules',
        'faq',
        'admin',
    ],
    'The shared public route table remains stable while catalogue categories become dynamic.'
);
$assert(
    in_array('categories', ADMIN_SECTIONS, true)
        && in_array('recommended', ADMIN_SECTIONS, true)
        && is_file($root . '/database/migrations/sqlite/004_dynamic_categories.php')
        && is_file($root . '/database/migrations/mysql/004_dynamic_categories.php')
        && is_file($root . '/public_html/actions/admin_delete_category.php')
        && is_file($root . '/public_html/category.php')
        && is_file($root . '/controllers/Store/category.php')
        && is_file($root . '/controllers/Store/catalog.php'),
    'Dynamic category administration, migrations and public storefront routing are present.'
);
