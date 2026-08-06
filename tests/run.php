<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$databasePath = $root . '/storage/test-suite.sqlite';
@unlink($databasePath);

$environment = [
    'APP_ENV' => 'test',
    'APP_KEY' => str_repeat('t', 64),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost:8000',
    'APP_FORCE_HTTPS' => 'false',
    'PAYMENTS_ENABLED' => 'false',
    'ALLOW_TEST_ORDERS' => 'true',
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
$_SERVER['SCRIPT_FILENAME'] = $root . '/public/index.php';

require_once $root . '/includes/config.php';
require_once $root . '/includes/routes.php';
require_once $root . '/includes/helpers.php';
require_once $root . '/includes/logging.php';
require_once $root . '/includes/session.php';
require_once $root . '/includes/database.php';
require_once $root . '/includes/migrations.php';
require_once $root . '/includes/i18n.php';
require_once $root . '/includes/catalog.php';
require_once $root . '/includes/coupons.php';
require_once $root . '/includes/cart.php';
require_once $root . '/includes/security.php';
require_once $root . '/includes/admin_auth.php';
require_once $root . '/includes/admin_products.php';

$tests = 0;
$failures = [];

$assert = static function (bool $condition, string $message) use (&$tests, &$failures): void {
    $tests++;

    if (!$condition) {
        $failures[] = $message;
    }
};

$throws = static function (callable $callback, string $message) use ($assert): void {
    try {
        $callback();
        $assert(false, $message);
    } catch (Throwable) {
        $assert(true, $message);
    }
};

$assert(parse_environment_line('VALUE="abc" # note') === ['VALUE', 'abc'], 'Quoted environment values ignore trailing comments.');
$assert(parse_environment_line('VALUE=abc # note') === ['VALUE', 'abc'], 'Unquoted environment values ignore whitespace comments.');
$assert(parse_environment_line('INVALID-NAME=value') === null, 'Invalid environment names are rejected.');
$assert(parse_money_to_cents('14,99') === 1499, 'Comma money values are converted to cents.');
$assert(parse_money_to_cents('14.9') === 1490, 'Single-decimal money values are normalized.');
$throws(static fn () => parse_money_to_cents('-1'), 'Negative money values are rejected.');
$assert(safe_return_path('https://example.com', 'cart') === 'cart', 'External return URLs are rejected.');
$assert(safe_return_path('/ranks.php?x=1', '') === 'ranks?x=1', 'Legacy public return paths are normalized.');
$assert(ip_matches_network('192.168.1.10', '192.168.1.0/24'), 'IPv4 CIDR matching works.');
$assert(!ip_matches_network('192.168.2.10', '192.168.1.0/24'), 'IPv4 CIDR mismatch is rejected.');
$assert(csrf_is_valid('token', 'token'), 'Valid CSRF tokens pass.');
$assert(!csrf_is_valid('wrong', 'token'), 'Invalid CSRF tokens fail.');
$assert(!admin_is_authenticated(), 'An empty session is not an authenticated administrator.');
$assert(configuration_errors() === [], 'The isolated test configuration is valid.');

$_GET = [
    'search' => '  vip  ',
    'category' => 'ranks',
    'state' => 'inactive',
];

$productFilters = admin_product_filters();

$assert(
    $productFilters === [
        'search' => 'vip',
        'category' => 'ranks',
        'state' => 'inactive',
    ],
    'Valid product filters are normalized.'
);

$productQuery = admin_products_query($productFilters);

$assert(
    str_contains(
        $productQuery['where'],
        'name LIKE :search_name'
    )
        && str_contains(
            $productQuery['where'],
            'category = :category'
        )
        && str_contains(
            $productQuery['where'],
            'active = :active'
        )
        && $productQuery['parameters'] === [
            'search_name' => '%vip%',
            'search_slug' => '%vip%',
            'search_tebex' => '%vip%',
            'category' => 'ranks',
            'active' => 0,
        ],
    'Product filters generate a parameterized query.'
);

$_GET = [
    'category' => 'invalid-category',
    'state' => 'invalid-state',
];

$assert(
    admin_product_filters() === [
        'search' => '',
        'category' => '',
        'state' => '',
    ],
    'Invalid product filters are discarded.'
);

$_GET = [];

$portugueseTranslations = require $root . '/translations/pt.php';
$englishTranslations = require $root . '/translations/en.php';
$assert(
    array_diff_key($portugueseTranslations, $englishTranslations) === []
        && array_diff_key($englishTranslations, $portugueseTranslations) === [],
    'Portuguese and English translation catalogues contain the same keys.'
);

$_SESSION['language'] = 'pt';
$assert(alternate_language() === 'en', 'Portuguese switches to English.');
$_SERVER['REQUEST_URI'] = '/admin?section=products';
$adminConfigured = true;
$adminAuthenticated = false;
ob_start();
require $root . '/templates/admin/page.php';
$adminLoginHtml = (string) ob_get_clean();
$assert(
    str_contains($adminLoginHtml, 'name="language" value="en"'),
    'The administrator login renders the language switch.'
);
$assert(
    str_contains($adminLoginHtml, 'name="return_to" value="admin?section=products"'),
    'The administrator language switch preserves the current section.'
);
$_SESSION = [];
$_SERVER['REQUEST_URI'] = '/';

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(STDOUT, 'Skipped database integration tests because pdo_sqlite is unavailable.' . PHP_EOL);
} else {
    migrate_database_cli(static function (): void {});
    require_once $root . '/database/seed.php';
    $pdo = db();
    $pdo->beginTransaction();
    seed_store_database($pdo);
    $pdo->commit();

    $assert((int) $pdo->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn() >= 3, 'All SQLite migrations are recorded.');
    $assert((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() > 0, 'The seed creates products.');
    $product = $pdo->query('SELECT * FROM products ORDER BY id LIMIT 1')->fetch();
    $productId = (int) $product['id'];
    $expectedPrice = (int) $product['price_cents'];
    cart_add($productId, 1);
    $summary = cart_summary();
    $assert((int) $summary['subtotal_cents'] === $expectedPrice, 'Cart totals use the database price.');

    $statement = $pdo->prepare('UPDATE products SET active = 0 WHERE id = :id');
    $statement->execute(['id' => $productId]);
    cart_clear();
    $throws(static fn () => cart_add($productId, 1), 'Inactive products cannot be added to the cart.');

    $now = date('Y-m-d H:i:s');
    $insertCoupon = $pdo->prepare(
        'INSERT INTO coupons
         (code, discount_type, discount_value, min_subtotal_cents, max_uses, used_count, active, expires_at, created_at, updated_at)
         VALUES
         (:code, :discount_type, :discount_value, 0, :max_uses, :used_count, 1, :expires_at, :created_at, :updated_at)'
    );
    $insertCoupon->execute([
        'code' => 'EXPIRED',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'max_uses' => null,
        'used_count' => 0,
        'expires_at' => date('Y-m-d H:i:s', time() - 60),
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $insertCoupon->execute([
        'code' => 'EXHAUSTED',
        'discount_type' => 'fixed',
        'discount_value' => 100,
        'max_uses' => 1,
        'used_count' => 1,
        'expires_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $throws(static fn () => validate_coupon('EXPIRED', 2000), 'Expired coupons are rejected.');
    $throws(static fn () => validate_coupon('EXHAUSTED', 2000), 'Exhausted coupons are rejected.');
    $assert((int) $pdo->query('SELECT COUNT(*) FROM admin_login_limits')->fetchColumn() === 0, 'The admin rate-limit table is available.');
}

@unlink($databasePath);

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }

    fwrite(STDERR, count($failures) . ' of ' . $tests . ' tests failed.' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Passed tests: ' . $tests . PHP_EOL);
exit(0);
