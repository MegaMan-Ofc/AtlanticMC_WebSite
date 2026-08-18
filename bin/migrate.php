<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/Core/Config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database/database.php';
require_once dirname(__DIR__) . '/app/Core/Database/migrations.php';

try {
    migrate_database_cli();
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
