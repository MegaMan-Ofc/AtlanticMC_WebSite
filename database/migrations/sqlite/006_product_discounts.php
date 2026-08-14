<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(products)')->fetchAll();

    foreach ($columns as $column) {
        if ((string) ($column['name'] ?? '') === 'discount_price_cents') {
            return;
        }
    }

    $pdo->exec(
        'ALTER TABLE products
         ADD COLUMN discount_price_cents INTEGER NULL'
    );
};
