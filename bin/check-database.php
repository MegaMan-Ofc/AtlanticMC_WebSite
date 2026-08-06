<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

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

    fwrite(STDOUT, 'Connection and migration ledger are ready.' . PHP_EOL);
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, 'Database check failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
