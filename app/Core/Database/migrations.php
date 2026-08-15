<?php

declare(strict_types=1);

function ensure_migration_table(PDO $pdo, string $driver): void
{
    if ($driver === 'mysql') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                migration VARCHAR(255) PRIMARY KEY,
                applied_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return;
    }

    if ($driver === 'sqlite') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                migration TEXT PRIMARY KEY,
                applied_at TEXT NOT NULL
            )'
        );
        return;
    }

    throw new RuntimeException('Unsupported database driver: ' . $driver);
}

function applied_migrations(PDO $pdo): array
{
    $rows = $pdo->query('SELECT migration FROM schema_migrations ORDER BY migration')->fetchAll(PDO::FETCH_COLUMN);

    return array_fill_keys(array_map('strval', $rows), true);
}

function migration_files(string $driver): array
{
    $directory = BASE_PATH . '/database/migrations/' . $driver;

    if (!is_dir($directory)) {
        throw new RuntimeException('Migration directory not found for driver: ' . $driver);
    }

    $files = array_merge(
        glob($directory . '/*.sql') ?: [],
        glob($directory . '/*.php') ?: []
    );
    sort($files, SORT_STRING);

    return $files;
}

function execute_sql_script(PDO $pdo, string $sql): void
{
    $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($sql));

    if ($statements === false) {
        throw new RuntimeException('Unable to parse migration SQL.');
    }

    foreach ($statements as $statement) {
        $statement = trim($statement);

        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

function run_migration_file(PDO $pdo, string $file): void
{
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($extension === 'sql') {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException('Unable to read migration: ' . basename($file));
        }

        execute_sql_script($pdo, $sql);
        return;
    }

    if ($extension === 'php') {
        $migration = require $file;

        if (!is_callable($migration)) {
            throw new RuntimeException('PHP migration must return a callable: ' . basename($file));
        }

        $migration($pdo);
        return;
    }

    throw new RuntimeException('Unsupported migration file: ' . basename($file));
}

function migrate_database_cli(?callable $output = null): int
{
    $output ??= static function (string $message): void {
        fwrite(STDOUT, $message . PHP_EOL);
    };

    $driver = (string) config('database.driver');
    $pdo = db();
    ensure_migration_table($pdo, $driver);
    $applied = applied_migrations($pdo);
    $count = 0;

    foreach (migration_files($driver) as $file) {
        $name = basename($file);

        if (isset($applied[$name])) {
            continue;
        }

        $output('Applying ' . $name);
        run_migration_file($pdo, $file);

        $statement = $pdo->prepare(
            'INSERT INTO schema_migrations (migration, applied_at)
             VALUES (:migration, :applied_at)'
        );
        $statement->execute([
            'migration' => $name,
            'applied_at' => date('Y-m-d H:i:s'),
        ]);
        $count++;
    }

    $output($count === 0 ? 'Database is up to date.' : 'Applied migrations: ' . $count);

    return $count;
}
