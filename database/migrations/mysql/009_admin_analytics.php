<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $paidAt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'paid_at'")->fetch();

    if ($paidAt === false) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN paid_at DATETIME NULL AFTER updated_at');
    }

    $pdo->exec("UPDATE orders SET paid_at = updated_at WHERE status = 'paid' AND paid_at IS NULL");

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_route_stats (
            visit_date DATE NOT NULL,
            route_key VARCHAR(190) NOT NULL,
            page_views INT UNSIGNED NOT NULL DEFAULT 0,
            unique_sessions INT UNSIGNED NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (visit_date, route_key),
            KEY idx_route_stats_route_date (route_key, visit_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_product_stats (
            visit_date DATE NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            impressions INT UNSIGNED NOT NULL DEFAULT 0,
            unique_sessions INT UNSIGNED NOT NULL DEFAULT 0,
            cart_additions INT UNSIGNED NOT NULL DEFAULT 0,
            cart_sessions INT UNSIGNED NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (visit_date, product_id),
            KEY idx_product_stats_product_date (product_id, visit_date),
            CONSTRAINT fk_daily_product_stats_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
};
