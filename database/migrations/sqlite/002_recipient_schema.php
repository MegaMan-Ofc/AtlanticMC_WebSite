<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columns = [];

    foreach ($pdo->query('PRAGMA table_info(orders)')->fetchAll() as $column) {
        if (isset($column['name']) && is_string($column['name'])) {
            $columns[$column['name']] = $column;
        }
    }

    if ($columns === []) {
        return;
    }

    $needsRebuild = isset($columns['user_id'])
        || isset($columns['minecraft_uuid'])
        || !isset($columns['minecraft_platform']);

    if (!$needsRebuild) {
        $pdo->exec('DROP TABLE IF EXISTS users');
        return;
    }

    $platformExpression = isset($columns['minecraft_platform'])
        ? "CASE WHEN minecraft_platform = 'bedrock' THEN 'bedrock' ELSE 'java' END"
        : "'java'";

    $pdo->exec('PRAGMA foreign_keys = OFF');

    try {
        $pdo->beginTransaction();
        $pdo->exec('DROP TABLE IF EXISTS orders_recipient_migration');
        $pdo->exec(
            "CREATE TABLE orders_recipient_migration (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                public_token TEXT NOT NULL UNIQUE,
                minecraft_name TEXT NOT NULL,
                minecraft_platform TEXT NOT NULL CHECK (minecraft_platform IN ('java', 'bedrock')),
                subtotal_cents INTEGER NOT NULL,
                discount_cents INTEGER NOT NULL DEFAULT 0,
                total_cents INTEGER NOT NULL,
                currency TEXT NOT NULL DEFAULT 'EUR',
                coupon_code TEXT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                provider TEXT NOT NULL DEFAULT 'local',
                provider_reference TEXT NULL,
                provider_checkout_url TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )"
        );
        $pdo->exec(
            'INSERT INTO orders_recipient_migration
             (id, public_token, minecraft_name, minecraft_platform, subtotal_cents, discount_cents, total_cents,
              currency, coupon_code, status, provider, provider_reference, provider_checkout_url, created_at, updated_at)
             SELECT id, public_token, minecraft_name, ' . $platformExpression . ', subtotal_cents, discount_cents, total_cents,
                    currency, coupon_code, status, provider, provider_reference, provider_checkout_url, created_at, updated_at
             FROM orders'
        );
        $pdo->exec('DROP TABLE orders');
        $pdo->exec('ALTER TABLE orders_recipient_migration RENAME TO orders');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_recipient_created ON orders(minecraft_name, created_at)');
        $pdo->exec('DROP TABLE IF EXISTS users');
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    } finally {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    if ($pdo->query('PRAGMA foreign_key_check')->fetchAll() !== []) {
        throw new RuntimeException('Database migration created foreign key violations.');
    }
};
