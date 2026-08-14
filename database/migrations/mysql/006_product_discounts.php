<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $column = $pdo->query("SHOW COLUMNS FROM products LIKE 'discount_price_cents'")->fetch();

    if ($column === false) {
        $pdo->exec(
            'ALTER TABLE products
             ADD COLUMN discount_price_cents INT UNSIGNED NULL AFTER price_cents'
        );
    }
};
