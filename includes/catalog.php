<?php

declare(strict_types=1);

function product_metadata(array $product): array
{
    $metadata = json_decode((string) ($product['metadata'] ?? '{}'), true);

    return is_array($metadata) ? $metadata : [];
}

function products_by_category(string $category, bool $includeInactive = false): array
{
    $storeCategory = store_category_by_slug($category, $includeInactive);

    if ($storeCategory === null) {
        return [];
    }

    $sql = 'SELECT p.* FROM products p WHERE p.category_id = :category_id';

    if (!$includeInactive) {
        $sql .= ' AND p.active = 1';
    }

    $sql .= ' ORDER BY p.sort_order ASC, p.id ASC';
    $statement = db()->prepare($sql);
    $statement->execute(['category_id' => (int) $storeCategory['id']]);

    return $statement->fetchAll();
}

function product_by_id(int $productId, bool $includeInactive = false): ?array
{
    $sql = 'SELECT p.*
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE p.id = :id';

    if (!$includeInactive) {
        $sql .= ' AND p.active = 1 AND c.active = 1';
    }

    $statement = db()->prepare($sql);
    $statement->execute(['id' => $productId]);
    $product = $statement->fetch();

    return is_array($product) ? $product : null;
}

function products_by_ids(array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));

    if ($ids === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $statement = db()->prepare(
        "SELECT p.*
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.active = 1
           AND c.active = 1
           AND p.id IN ($placeholders)"
    );
    $statement->execute($ids);
    $products = [];

    foreach ($statement->fetchAll() as $product) {
        $products[(int) $product['id']] = $product;
    }

    return $products;
}

function category_configuration(string $category): array
{
    $configurations = [
        'ranks' => [
            'title' => 'Atlantic SMP - ' . localized_category('ranks'),
            'heading' => localized_category('ranks'),
            'description' => t('catalog.ranks.description'),
            'bodyClass' => 'page-ranks',
            'styles' => ['css/pages/catalog.css', 'css/pages/ranks.css'],
        ],
        'rubis' => [
            'title' => 'Atlantic SMP - ' . localized_category('rubis'),
            'heading' => localized_category('rubis'),
            'description' => t('catalog.rubis.description'),
            'bodyClass' => 'page-rubis',
            'styles' => ['css/pages/catalog.css'],
        ],
        'keys' => [
            'title' => 'Atlantic SMP - ' . localized_category('keys'),
            'heading' => localized_category('keys'),
            'description' => t('catalog.keys.description'),
            'bodyClass' => 'page-keys',
            'styles' => ['css/pages/catalog.css'],
        ],
        'boosters' => [
            'title' => t('catalog.boosters.title'),
            'heading' => t('catalog.boosters.heading'),
            'description' => t('catalog.boosters.description'),
            'bodyClass' => 'page-boosters',
            'styles' => ['css/pages/catalog.css'],
        ],
    ];

    if (!isset($configurations[$category])) {
        throw new InvalidArgumentException(t('catalog.unknown_category'));
    }

    return $configurations[$category];
}
