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

    execute_sql_script(db(), $schema);
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
