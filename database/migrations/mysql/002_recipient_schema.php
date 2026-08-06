<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columnExists = static function (string $table, string $column) use ($pdo): bool {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name'
        );
        $statement->execute([
            'table_name' => $table,
            'column_name' => $column,
        ]);

        return (int) $statement->fetchColumn() > 0;
    };

    $indexExists = static function (string $table, string $index) use ($pdo): bool {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name'
        );
        $statement->execute([
            'table_name' => $table,
            'index_name' => $index,
        ]);

        return (int) $statement->fetchColumn() > 0;
    };

    if (!$columnExists('orders', 'minecraft_platform')) {
        $pdo->exec(
            "ALTER TABLE orders ADD COLUMN minecraft_platform ENUM('java', 'bedrock') NOT NULL DEFAULT 'java' AFTER minecraft_name"
        );
    }

    if ($columnExists('orders', 'user_id')) {
        $statement = $pdo->prepare(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name
               AND REFERENCED_TABLE_NAME IS NOT NULL'
        );
        $statement->execute([
            'table_name' => 'orders',
            'column_name' => 'user_id',
        ]);

        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $constraint) {
            if (is_string($constraint) && preg_match('/^[A-Za-z0-9_]+$/', $constraint)) {
                $pdo->exec('ALTER TABLE orders DROP FOREIGN KEY `' . $constraint . '`');
            }
        }

        if ($indexExists('orders', 'idx_orders_user_created')) {
            $pdo->exec('ALTER TABLE orders DROP INDEX idx_orders_user_created');
        }

        $pdo->exec('ALTER TABLE orders DROP COLUMN user_id');
    }

    if ($columnExists('orders', 'minecraft_uuid')) {
        $pdo->exec('ALTER TABLE orders DROP COLUMN minecraft_uuid');
    }

    if (!$indexExists('orders', 'idx_orders_recipient_created')) {
        $pdo->exec('ALTER TABLE orders ADD INDEX idx_orders_recipient_created (minecraft_name, created_at)');
    }

    $pdo->exec('DROP TABLE IF EXISTS users');
};
