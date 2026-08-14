<?php

declare(strict_types=1);

$recommendedColumns = array_column(
    $pdo->query('PRAGMA table_info(recommended_products)')->fetchAll(),
    'name'
);
$assert(
    in_array('slot', $recommendedColumns, true)
        && in_array('product_id', $recommendedColumns, true),
    'The recommended products migration creates ordered product slots.'
);

$activeProductIds = array_map(
    'intval',
    $pdo->query(
        'SELECT p.id
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.active = 1 AND c.active = 1
         ORDER BY p.id ASC
         LIMIT 3'
    )->fetchAll(PDO::FETCH_COLUMN)
);

if (count($activeProductIds) >= 3) {
    save_recommended_product(1, $activeProductIds[0]);
    save_recommended_product(2, $activeProductIds[1]);
    save_recommended_product(3, $activeProductIds[2]);

    $slots = admin_recommended_slots();
    $assert(
        (int) $slots[1]['id'] === $activeProductIds[0]
            && (int) $slots[2]['id'] === $activeProductIds[1]
            && (int) $slots[3]['id'] === $activeProductIds[2]
            && $slots[4] === null
            && $slots[5] === null,
        'Administrators can fill individual recommended product slots.'
    );

    reorder_recommended_products([
        $activeProductIds[2],
        $activeProductIds[0],
        $activeProductIds[1],
        0,
        0,
    ]);

    $ordered = recommended_products(false);
    $assert(
        array_map(static fn (array $product): int => (int) $product['id'], $ordered) === [
            $activeProductIds[2],
            $activeProductIds[0],
            $activeProductIds[1],
        ],
        'Recommended products can be reordered while preserving empty slots.'
    );

    save_recommended_product(5, $activeProductIds[0]);
    $slots = admin_recommended_slots();
    $assert(
        $slots[2] === null
            && (int) $slots[5]['id'] === $activeProductIds[0],
        'Choosing an already recommended product moves it to the selected slot instead of duplicating it.'
    );

    remove_recommended_product(5);
    $slots = admin_recommended_slots();
    $assert(
        $slots[5] === null,
        'Recommended products can be removed from individual slots.'
    );

    $throws(
        static fn () => reorder_recommended_products([$activeProductIds[1], $activeProductIds[1], 0, 0, 0]),
        'Recommended product ordering rejects duplicate products.'
    );
}
