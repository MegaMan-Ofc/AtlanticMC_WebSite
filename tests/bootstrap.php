<?php

declare(strict_types=1);

require_once __DIR__ . '/support/TestSuite.php';

$root = dirname(__DIR__);
$databasePath = $root . '/storage/test-suite.sqlite';
@unlink($databasePath);

$environment = [
    'APP_ENV' => 'test',
    'APP_KEY' => str_repeat('t', 64),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost:8000',
    'APP_FORCE_HTTPS' => 'false',
    'SERVER_IP' => 'play.atlanticeu.online',
    'BEDROCK_SERVER_IP' => 'play.atlanticeu.online',
    'BEDROCK_SERVER_PORT' => '19132',
    'BEDROCK_USERNAME_PREFIX' => '.',
    'PAYMENTS_ENABLED' => 'false',
    'ALLOW_TEST_ORDERS' => 'true',
    'TEBEX_WEBHOOK_SECRET' => 'test-webhook-secret-0123456789',
    'TEBEX_VERIFY_WEBHOOK_AMOUNT' => 'true',
    'TEBEX_COUPONS_ENABLED' => 'false',
    'DB_DRIVER' => 'sqlite',
    'DB_PATH' => $databasePath,
    'ADMIN_USERNAME' => 'test-admin',
    'ADMIN_PASSWORD_HASH' => password_hash('test-password', PASSWORD_DEFAULT),
];

foreach ($environment as $name => $value) {
    putenv($name . '=' . $value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

$_SESSION = [];
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/index.php';

foreach ([
    'config',
    'routes',
    'helpers',
    'logging',
    'session',
    'database',
    'migrations',
    'i18n',
    'categories',
    'catalog',
    'recommended',
    'coupons',
    'cart',
    'security',
    'admin_auth',
    'admin_formatting',
    'media',
    'admin_categories',
    'admin_products',
    'admin_recommended',
    'admin_home',
    'admin_coupons',
    'admin_orders',
    'admin_dashboard',
    'minecraft_recipient',
    'tebex',
    'orders',
] as $include) {
    require_once $root . '/includes/' . $include . '.php';
}

$suite = new TestSuite();

$assert = static function (bool $condition, string $message) use ($suite): void {
    $suite->assert($condition, $message);
};

$throws = static function (callable $callback, string $message) use ($suite): void {
    $suite->throws($callback, $message);
};
