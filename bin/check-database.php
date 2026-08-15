<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/migrations.php';

try {
    $pdo = db();
    $driver = (string) config('database.driver');
    $pdo->query('SELECT 1')->fetchColumn();

    if ($driver === 'mysql') {
        $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
        $charset = (string) $pdo->query('SELECT @@character_set_connection')->fetchColumn();
        $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

        if ($charset !== 'utf8mb4') {
            throw new RuntimeException('MySQL connection charset is not utf8mb4.');
        }

        fwrite(STDOUT, 'Driver: mysql' . PHP_EOL);
        fwrite(STDOUT, 'Database: ' . $database . PHP_EOL);
        fwrite(STDOUT, 'Version: ' . $version . PHP_EOL);
        fwrite(STDOUT, 'Charset: ' . $charset . PHP_EOL);
    } else {
        fwrite(STDOUT, 'Driver: sqlite' . PHP_EOL);
        fwrite(STDOUT, 'Database: ' . (string) config('database.sqlite_path') . PHP_EOL);
    }

    $migrationTable = $driver === 'mysql'
        ? "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'schema_migrations'"
        : "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'schema_migrations'";

    if ((int) $pdo->query($migrationTable)->fetchColumn() !== 1) {
        throw new RuntimeException('Database migrations have not been applied.');
    }

    $expected = array_map('basename', migration_files($driver));
    $applied = array_keys(applied_migrations($pdo));
    $missing = array_values(array_diff($expected, $applied));
    $unexpected = array_values(array_diff($applied, $expected));

    fwrite(STDOUT, 'Expected migrations: ' . count($expected) . PHP_EOL);
    fwrite(STDOUT, 'Applied migrations: ' . count($applied) . PHP_EOL);

    if ($missing !== []) {
        fwrite(STDERR, 'Missing migrations:' . PHP_EOL);
        foreach ($missing as $migration) {
            fwrite(STDERR, '  - ' . $migration . PHP_EOL);
        }

        throw new RuntimeException('Database schema is behind the application. Run php bin/migrate.php.');
    }

    if ($unexpected !== []) {
        fwrite(STDOUT, 'Migration ledger contains files no longer present in the repository:' . PHP_EOL);
        foreach ($unexpected as $migration) {
            fwrite(STDOUT, '  - ' . $migration . PHP_EOL);
        }
    }

    fwrite(STDOUT, 'Connection and migration ledger are ready.' . PHP_EOL);
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, 'Database check failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
