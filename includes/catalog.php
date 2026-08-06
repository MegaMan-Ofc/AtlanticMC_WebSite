<?php

declare(strict_types=1);

function product_metadata(array $product): array
{
    $metadata = json_decode((string) ($product['metadata'] ?? '{}'), true);

    return is_array($metadata) ? $metadata : [];
}

function products_by_category(string $category, bool $includeInactive = false): array
{
    if (!in_array($category, STORE_CATEGORIES, true)) {
        return [];
    }

    $sql = 'SELECT * FROM products WHERE category = :category';

    if (!$includeInactive) {
        $sql .= ' AND active = 1';
    }

    $sql .= ' ORDER BY sort_order ASC, id ASC';
    $statement = db()->prepare($sql);
    $statement->execute(['category' => $category]);

    return $statement->fetchAll();
}


function product_by_id(int $productId, bool $includeInactive = false): ?array
{
    $sql = "SELECT * FROM products WHERE id = :id AND category IN ('ranks', 'rubis', 'keys', 'boosters')";

    if (!$includeInactive) {
        $sql .= ' AND active = 1';
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
    $statement = db()->prepare("SELECT * FROM products WHERE active = 1 AND category IN ('ranks', 'rubis', 'keys', 'boosters') AND id IN ($placeholders)");
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
            'title' => 'Atlantic Anarchy - ' . localized_category('ranks'),
            'heading' => localized_category('ranks'),
            'description' => t('catalog.ranks.description'),
            'bodyClass' => 'page-ranks',
            'styles' => ['css/pages/catalog.css', 'css/pages/ranks.css'],
        ],
        'rubis' => [
            'title' => 'Atlantic Anarchy - ' . localized_category('rubis'),
            'heading' => localized_category('rubis'),
            'description' => t('catalog.rubis.description'),
            'bodyClass' => 'page-rubis',
            'styles' => ['css/pages/catalog.css'],
        ],
        'keys' => [
            'title' => 'Atlantic Anarchy - ' . localized_category('keys'),
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
