<?php

declare(strict_types=1);

$_GET = [
    'search' => '  vip  ',
    'category_id' => '2',
    'state' => 'inactive',
    'sort' => 'price_desc',
];

$productFilters = admin_product_filters();

$assert(
    $productFilters === [
        'search' => 'vip',
        'category_id' => 2,
        'state' => 'inactive',
        'sort' => 'price_desc',
    ],
    'Valid product filters and sorting are normalized.'
);

$productQuery = admin_products_query($productFilters);

$assert(
    str_contains(
        $productQuery['where'],
        'name LIKE :search_name'
    )
        && str_contains(
            $productQuery['where'],
            'p.category_id = :category_id'
        )
        && str_contains(
            $productQuery['where'],
            'active = :active'
        )
        && $productQuery['parameters'] === [
            'search_name' => '%vip%',
            'search_slug' => '%vip%',
            'search_tebex' => '%vip%',
            'category_id' => 2,
            'active' => 0,
        ],
    'Product filters generate a parameterized query.'
);

$_GET = [
    'category_id' => '-4',
    'state' => 'invalid-state',
    'sort' => 'invalid-sort',
];

$assert(
    admin_product_filters() === [
        'search' => '',
        'category_id' => 0,
        'state' => '',
        'sort' => '',
    ],
    'Invalid product filters are discarded.'
);

$_GET = [];

$assert(
    admin_product_query_parameters([
        'search' => 'vip',
        'category_id' => 0,
        'state' => 'active',
        'sort' => 'name_asc',
    ]) === [
        'search' => 'vip',
        'state' => 'active',
        'sort' => 'name_asc',
    ],
    'Product filter URLs omit empty values.'
);

$assert(
    admin_product_order_by(['sort' => 'price_desc']) === 'COALESCE(p.discount_price_cents, p.price_cents) DESC, p.id DESC'
        && admin_product_order_by(['sort' => 'invalid']) === 'c.sort_order ASC, c.id ASC, p.sort_order ASC, p.id ASC',
    'Product sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'player' => ' Steve ',
    'status' => 'paid',
    'order' => 'abc',
    'date_from' => '2026-01-01',
    'date_to' => '2026-01-31',
    'sort' => 'total_desc',
    'page' => '2',
];

$orderFilters = admin_order_filters();

$assert(
    $orderFilters === [
        'player' => 'Steve',
        'status' => 'paid',
        'order' => 'abc',
        'date_from' => '2026-01-01',
        'date_to' => '2026-01-31',
        'sort' => 'total_desc',
        'page' => 2,
    ],
    'Valid order filters and sorting are normalized.'
);

$assert(
    admin_order_order_by($orderFilters) === 'total_cents DESC, id DESC'
        && admin_order_order_by(['sort' => 'invalid']) === 'created_at DESC, id DESC',
    'Order sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'search' => ' save ',
    'type' => 'percentage',
    'state' => 'available',
    'sort' => 'usage_desc',
];

$couponFilters = admin_coupon_filters();
$couponQuery = admin_coupons_query($couponFilters);

$assert(
    $couponFilters === [
        'search' => 'save',
        'type' => 'percentage',
        'state' => 'available',
        'sort' => 'usage_desc',
    ]
        && str_contains($couponQuery['where'], 'code LIKE :search')
        && str_contains($couponQuery['where'], 'discount_type = :discount_type')
        && str_contains($couponQuery['where'], 'used_count < max_uses')
        && $couponQuery['parameters']['search'] === '%SAVE%'
        && $couponQuery['parameters']['discount_type'] === 'percentage',
    'Coupon filters generate a parameterized query.'
);

$assert(
    admin_coupon_order_by($couponFilters) === 'used_count DESC, id DESC'
        && admin_coupon_order_by(['sort' => 'invalid']) === 'created_at DESC, id DESC',
    'Coupon sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'type' => 'invalid',
    'state' => 'invalid',
    'sort' => 'invalid',
];

$assert(
    admin_coupon_filters() === [
        'search' => '',
        'type' => '',
        'state' => '',
        'sort' => '',
    ],
    'Invalid coupon filters and sorting are discarded.'
);

$_GET = [];

