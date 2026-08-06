<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/database/seed.php';

$pdo = db();
$startedTransaction = !$pdo->inTransaction();

try {
    if ($startedTransaction) {
        $pdo->beginTransaction();
    }

    seed_store_database($pdo);

    if ($startedTransaction && $pdo->inTransaction()) {
        $pdo->commit();
    }

    fwrite(STDOUT, 'Seed completed.' . PHP_EOL);
    exit(0);
} catch (Throwable $error) {
    if ($startedTransaction && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Seed failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
