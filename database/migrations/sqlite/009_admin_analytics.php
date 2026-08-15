<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $orderColumns = array_column($pdo->query('PRAGMA table_info(orders)')->fetchAll(), 'name');

    if (!in_array('paid_at', $orderColumns, true)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN paid_at TEXT NULL');
    }

    $pdo->exec("UPDATE orders SET paid_at = updated_at WHERE status = 'paid' AND paid_at IS NULL");

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_route_stats (
            visit_date TEXT NOT NULL,
            route_key TEXT NOT NULL,
            page_views INTEGER NOT NULL DEFAULT 0,
            unique_sessions INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL,
            PRIMARY KEY (visit_date, route_key)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_product_stats (
            visit_date TEXT NOT NULL,
            product_id INTEGER NOT NULL,
            impressions INTEGER NOT NULL DEFAULT 0,
            unique_sessions INTEGER NOT NULL DEFAULT 0,
            cart_additions INTEGER NOT NULL DEFAULT 0,
            cart_sessions INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL,
            PRIMARY KEY (visit_date, product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_route_stats_route_date ON daily_route_stats(route_key, visit_date)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_product_stats_product_date ON daily_product_stats(product_id, visit_date)');
};
