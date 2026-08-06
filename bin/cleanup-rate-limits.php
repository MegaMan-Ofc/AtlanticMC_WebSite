<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

try {
    $retention = max(86400, (int) ($argv[1] ?? 604800));
    $threshold = date('Y-m-d H:i:s', time() - $retention);
    $statement = db()->prepare('DELETE FROM admin_login_limits WHERE updated_at < :threshold');
    $statement->execute(['threshold' => $threshold]);
    fwrite(STDOUT, 'Removed rate-limit rows: ' . $statement->rowCount() . PHP_EOL);
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, 'Rate-limit cleanup failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
