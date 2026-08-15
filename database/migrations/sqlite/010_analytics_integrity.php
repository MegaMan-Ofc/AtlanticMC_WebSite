<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $siteColumns = array_column($pdo->query('PRAGMA table_info(daily_site_stats)')->fetchAll(), 'name');

    if (!in_array('product_sessions', $siteColumns, true)) {
        $pdo->exec('ALTER TABLE daily_site_stats ADD COLUMN product_sessions INTEGER NOT NULL DEFAULT 0');
    }

    if (!in_array('cart_sessions', $siteColumns, true)) {
        $pdo->exec('ALTER TABLE daily_site_stats ADD COLUMN cart_sessions INTEGER NOT NULL DEFAULT 0');
    }

    $productStatColumns = array_column($pdo->query('PRAGMA table_info(daily_product_stats)')->fetchAll(), 'name');

    if (!in_array('interactions', $productStatColumns, true)) {
        $pdo->exec('ALTER TABLE daily_product_stats ADD COLUMN interactions INTEGER NOT NULL DEFAULT 0');
    }

    if (!in_array('interaction_sessions', $productStatColumns, true)) {
        $pdo->exec('ALTER TABLE daily_product_stats ADD COLUMN interaction_sessions INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_status_paid_at ON orders(status, paid_at)');

    $pdo->exec(
        'UPDATE products
         SET category_id = (
             SELECT categories.id
             FROM categories
             WHERE categories.slug = products.category
         )
         WHERE category_id IS NULL
           AND EXISTS (
             SELECT 1 FROM categories WHERE categories.slug = products.category
           )'
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

    $pdo->exec('DROP TRIGGER IF EXISTS trg_products_category_insert');
    $pdo->exec('DROP TRIGGER IF EXISTS trg_products_category_update');
    $pdo->exec('DROP TRIGGER IF EXISTS trg_categories_products_delete');

    $pdo->exec(
        "CREATE TRIGGER trg_products_category_insert
         BEFORE INSERT ON products
         FOR EACH ROW
         WHEN NEW.category_id IS NULL
          OR NOT EXISTS (SELECT 1 FROM categories WHERE id = NEW.category_id)
         BEGIN
             SELECT RAISE(ABORT, 'Invalid product category_id');
         END"
    );

    $pdo->exec(
        "CREATE TRIGGER trg_products_category_update
         BEFORE UPDATE OF category_id ON products
         FOR EACH ROW
         WHEN NEW.category_id IS NULL
          OR NOT EXISTS (SELECT 1 FROM categories WHERE id = NEW.category_id)
         BEGIN
             SELECT RAISE(ABORT, 'Invalid product category_id');
         END"
    );

    $pdo->exec(
        "CREATE TRIGGER IF NOT EXISTS trg_categories_products_delete
         BEFORE DELETE ON categories
         FOR EACH ROW
         WHEN EXISTS (SELECT 1 FROM products WHERE category_id = OLD.id)
         BEGIN
             SELECT RAISE(ABORT, 'Category still has products');
         END"
    );
};
