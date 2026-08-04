<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $driver = (string) config('database.driver', 'sqlite');

    if ($driver === 'sqlite') {
        $path = (string) config('database.sqlite_path');
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the database storage directory.');
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    } elseif ($driver === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            config('database.host'),
            config('database.port'),
            config('database.name'),
            config('database.charset')
        );

        $pdo = new PDO($dsn, (string) config('database.user'), (string) config('database.password'));
    } else {
        throw new RuntimeException('Unsupported database driver: ' . $driver);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $pdo;
}

function execute_sql_script(PDO $pdo, string $schema): void
{
    $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($schema));

    if ($statements === false) {
        throw new RuntimeException('Unable to parse database schema.');
    }

    foreach ($statements as $statement) {
        $statement = trim($statement);

        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

function sqlite_table_columns(PDO $pdo, string $table): array
{
    $statement = $pdo->query('PRAGMA table_info(' . $table . ')');
    $columns = [];

    foreach ($statement->fetchAll() as $column) {
        if (isset($column['name']) && is_string($column['name'])) {
            $columns[$column['name']] = $column;
        }
    }

    return $columns;
}

function migrate_sqlite_recipient_schema(PDO $pdo): void
{
    $columns = sqlite_table_columns($pdo, 'orders');

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

    $violations = $pdo->query('PRAGMA foreign_key_check')->fetchAll();

    if ($violations !== []) {
        throw new RuntimeException('Database migration created foreign key violations.');
    }
}

function mysql_column_exists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name'
    );
    $statement->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function mysql_index_exists(PDO $pdo, string $table, string $index): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name'
    );
    $statement->execute([
        'table_name' => $table,
        'index_name' => $index,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function migrate_mysql_recipient_schema(PDO $pdo): void
{
    if (!mysql_column_exists($pdo, 'orders', 'minecraft_platform')) {
        $pdo->exec(
            "ALTER TABLE orders ADD COLUMN minecraft_platform ENUM('java', 'bedrock') NOT NULL DEFAULT 'java' AFTER minecraft_name"
        );
    }

    if (mysql_column_exists($pdo, 'orders', 'user_id')) {
        $constraintStatement = $pdo->prepare(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name
               AND REFERENCED_TABLE_NAME IS NOT NULL'
        );
        $constraintStatement->execute([
            'table_name' => 'orders',
            'column_name' => 'user_id',
        ]);

        foreach ($constraintStatement->fetchAll(PDO::FETCH_COLUMN) as $constraint) {
            if (is_string($constraint) && preg_match('/^[A-Za-z0-9_]+$/', $constraint)) {
                $pdo->exec('ALTER TABLE orders DROP FOREIGN KEY `' . $constraint . '`');
            }
        }

        if (mysql_index_exists($pdo, 'orders', 'idx_orders_user_created')) {
            $pdo->exec('ALTER TABLE orders DROP INDEX idx_orders_user_created');
        }

        $pdo->exec('ALTER TABLE orders DROP COLUMN user_id');
    }

    if (mysql_column_exists($pdo, 'orders', 'minecraft_uuid')) {
        $pdo->exec('ALTER TABLE orders DROP COLUMN minecraft_uuid');
    }

    if (!mysql_index_exists($pdo, 'orders', 'idx_orders_recipient_created')) {
        $pdo->exec('ALTER TABLE orders ADD INDEX idx_orders_recipient_created (minecraft_name, created_at)');
    }

    $pdo->exec('DROP TABLE IF EXISTS users');
}

function migrate_database(PDO $pdo, string $driver): void
{
    if ($driver === 'sqlite') {
        migrate_sqlite_recipient_schema($pdo);
        return;
    }

    if ($driver === 'mysql') {
        migrate_mysql_recipient_schema($pdo);
    }
}

function initialize_database(): void
{
    if (!(bool) config('app.auto_migrate', true)) {
        return;
    }

    $driver = (string) config('database.driver', 'sqlite');
    $schemaFile = BASE_PATH . '/database/schema_' . $driver . '.sql';

    if (!is_file($schemaFile)) {
        throw new RuntimeException('Database schema not found for driver: ' . $driver);
    }

    $schema = file_get_contents($schemaFile);

    if ($schema === false) {
        throw new RuntimeException('Unable to read database schema.');
    }

    $pdo = db();
    execute_sql_script($pdo, $schema);
    migrate_database($pdo, $driver);
    seed_database();
}

function seed_database(): void
{
    require_once BASE_PATH . '/database/seed.php';
    $pdo = db();
    $startedTransaction = !$pdo->inTransaction();

    if ($startedTransaction) {
        $pdo->beginTransaction();
    }

    try {
        seed_store_database($pdo);

        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $error) {
        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}
