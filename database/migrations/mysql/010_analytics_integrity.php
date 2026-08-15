<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $siteColumns = [];
    foreach ($pdo->query('SHOW COLUMNS FROM daily_site_stats')->fetchAll() as $column) {
        $siteColumns[(string) $column['Field']] = true;
    }

    if (!isset($siteColumns['product_sessions'])) {
        $pdo->exec('ALTER TABLE daily_site_stats ADD COLUMN product_sessions INT UNSIGNED NOT NULL DEFAULT 0 AFTER unique_sessions');
    }

    if (!isset($siteColumns['cart_sessions'])) {
        $pdo->exec('ALTER TABLE daily_site_stats ADD COLUMN cart_sessions INT UNSIGNED NOT NULL DEFAULT 0 AFTER product_sessions');
    }

    $productStatColumns = [];
    foreach ($pdo->query('SHOW COLUMNS FROM daily_product_stats')->fetchAll() as $column) {
        $productStatColumns[(string) $column['Field']] = true;
    }

    if (!isset($productStatColumns['interactions'])) {
        $pdo->exec('ALTER TABLE daily_product_stats ADD COLUMN interactions INT UNSIGNED NOT NULL DEFAULT 0 AFTER unique_sessions');
    }

    if (!isset($productStatColumns['interaction_sessions'])) {
        $pdo->exec('ALTER TABLE daily_product_stats ADD COLUMN interaction_sessions INT UNSIGNED NOT NULL DEFAULT 0 AFTER interactions');
    }

    $paidIndex = $pdo->query("SHOW INDEX FROM orders WHERE Key_name = 'idx_orders_status_paid_at'")->fetch();
    if ($paidIndex === false) {
        $pdo->exec('CREATE INDEX idx_orders_status_paid_at ON orders(status, paid_at)');
    }

    $pdo->exec(
        'UPDATE products p
         INNER JOIN categories c ON c.slug = p.category
         SET p.category_id = c.id
         WHERE p.category_id IS NULL'
    );

    $invalidProducts = (int) $pdo->query(
        'SELECT COUNT(*)
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.category_id IS NULL OR c.id IS NULL'
    )->fetchColumn();

    if ($invalidProducts > 0) {
        throw new RuntimeException('Cannot enforce product category integrity while products without a valid category exist.');
    }

    $pdo->exec('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NOT NULL');

    $foreignKey = $pdo->query(
        "SELECT CONSTRAINT_NAME
         FROM information_schema.REFERENTIAL_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND TABLE_NAME = 'products'
           AND CONSTRAINT_NAME = 'fk_products_category'"
    )->fetch();

    if ($foreignKey === false) {
        $pdo->exec(
            'ALTER TABLE products
             ADD CONSTRAINT fk_products_category
             FOREIGN KEY (category_id) REFERENCES categories(id)
             ON UPDATE CASCADE
             ON DELETE RESTRICT'
        );
    }
};
