<?php

declare(strict_types=1);

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(STDOUT, 'Skipped database integration tests because pdo_sqlite is unavailable.' . PHP_EOL);
    return;
}

migrate_database_cli(static function (): void {});
require_once $root . '/database/seed.php';

$pdo = db();
$pdo->beginTransaction();
seed_store_database($pdo);
$pdo->commit();

$assert((int) $pdo->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn() >= 9, 'All SQLite migrations are recorded.');

$orderColumns = array_column($pdo->query('PRAGMA table_info(orders)')->fetchAll(), 'name');
$assert(
    in_array('tebex_total_cents', $orderColumns, true)
        && in_array('tebex_currency', $orderColumns, true),
    'The Tebex hardening migration stores provider basket totals on orders.'
);


$assert(
    in_array('paid_at', $orderColumns, true),
    'The analytics migration stores the original paid timestamp on orders.'
);
$assert(
    (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name IN ('daily_route_stats', 'daily_product_stats')")->fetchColumn() === 2,
    'The analytics migration creates route and product daily statistic tables.'
);

$productColumns = array_column($pdo->query('PRAGMA table_info(products)')->fetchAll(), 'name');
$assert(
    in_array('discount_price_cents', $productColumns, true),
    'The product discount migration adds the promotional price column.'
);

$assert((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() > 0, 'The seed creates products.');
$assert((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() >= 4, 'The dynamic category migration creates catalogue categories.');

$categoryColumns = array_column($pdo->query('PRAGMA table_info(categories)')->fetchAll(), 'name');
$assert(
    in_array('home_placement', $categoryColumns, true)
        && in_array('home_sort_order', $categoryColumns, true),
    'The homepage category layout migration adds placement and independent homepage ordering.'
);
$assert((int) $pdo->query('SELECT COUNT(*) FROM products WHERE category_id IS NULL')->fetchColumn() === 0, 'Seed products are linked to dynamic category IDs.');

require __DIR__ . '/ProductSearchTest.php';
require __DIR__ . '/CategoryProductTest.php';
require __DIR__ . '/CommerceTest.php';
require __DIR__ . '/RecommendedProductsTest.php';
require __DIR__ . '/HomeCategoryLayoutTest.php';
