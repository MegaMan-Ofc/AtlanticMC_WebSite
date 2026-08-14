<?php

declare(strict_types=1);

const RECOMMENDED_PRODUCT_SLOTS = 5;

function recommended_products(bool $includeInactive = false): array
{
    $sql = 'SELECT r.slot,
                   p.*,
                   c.slug AS category_slug,
                   c.name AS category_name,
                   c.active AS category_active
            FROM recommended_products r
            INNER JOIN products p ON p.id = r.product_id
            INNER JOIN categories c ON c.id = p.category_id';

    if (!$includeInactive) {
        $sql .= ' WHERE p.active = 1 AND c.active = 1';
    }

    $sql .= ' ORDER BY r.slot ASC';

    return db()->query($sql)->fetchAll();
}

function recommended_products_for_home(): array
{
    return recommended_products(false);
}

function recommended_product_ids(): array
{
    return array_map(
        static fn (array $product): int => (int) $product['id'],
        recommended_products(true)
    );
}
