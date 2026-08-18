<?php

declare(strict_types=1);

$discountProduct = [
    'price_cents' => 1000,
    'discount_price_cents' => 750,
];

$assert(
    product_has_discount($discountProduct)
        && product_effective_price_cents($discountProduct) === 750
        && product_discount_percentage($discountProduct) === 25,
    'Product discount helpers expose the promotional price without changing the regular price.'
);

$assert(
    !product_has_discount([
        'price_cents' => 1000,
        'discount_price_cents' => 1000,
    ]),
    'Invalid promotional prices are ignored by public price helpers.'
);
