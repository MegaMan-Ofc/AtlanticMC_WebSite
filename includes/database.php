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

        $pdo = new PDO(
            $dsn,
            (string) config('database.user'),
            (string) config('database.password'),
            [PDO::ATTR_TIMEOUT => (int) config('database.connect_timeout', 5)]
        );
    } else {
        throw new RuntimeException('Unsupported database driver: ' . $driver);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $pdo;
}
