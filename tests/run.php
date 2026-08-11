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
    'SERVER_IP' => 'play.atlanticeu.online',
    'BEDROCK_SERVER_IP' => 'play.atlanticeu.online',
    'BEDROCK_SERVER_PORT' => '19132',
    'BEDROCK_USERNAME_PREFIX' => '.',
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
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/index.php';

require_once $root . '/includes/config.php';
require_once $root . '/includes/routes.php';
require_once $root . '/includes/helpers.php';
require_once $root . '/includes/logging.php';
require_once $root . '/includes/session.php';
require_once $root . '/includes/database.php';
require_once $root . '/includes/migrations.php';
require_once $root . '/includes/i18n.php';
require_once $root . '/includes/categories.php';
require_once $root . '/includes/catalog.php';
require_once $root . '/includes/coupons.php';
require_once $root . '/includes/cart.php';
require_once $root . '/includes/security.php';
require_once $root . '/includes/admin_auth.php';
require_once $root . '/includes/admin_formatting.php';
require_once $root . '/includes/admin_categories.php';
require_once $root . '/includes/admin_products.php';
require_once $root . '/includes/admin_coupons.php';
require_once $root . '/includes/admin_orders.php';
require_once $root . '/includes/admin_dashboard.php';
require_once $root . '/includes/minecraft_recipient.php';

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
$assert(
    normalize_minecraft_username('Java_User', 'java') === 'Java_User'
        && normalize_minecraft_username('Bed Rock', 'bedrock') === 'Bed Rock'
        && minecraft_server_username('Bed Rock', 'bedrock') === '.Bed_Rock',
    'Java and Bedrock recipients normalize to the expected server usernames.'
);
$throws(
    static fn () => normalize_minecraft_username('ab', 'java'),
    'Invalid Java usernames are rejected.'
);
$throws(
    static fn () => normalize_minecraft_username('Bed-Rock!', 'bedrock'),
    'Invalid Bedrock Gamertags are rejected.'
);

$assert(
    public_route_name_from_request_uri('/') === 'home'
        && public_route_name_from_request_uri('/admin') === 'admin'
        && public_route_name_from_request_uri('/purchase-policy?from=footer') === 'purchase-policy'
        && public_route_name_from_request_uri('/does-not-exist') === null,
    'Clean request paths resolve through the shared public route table.'
);

$assert(
    STORE_CATEGORIES === ['ranks', 'rubis', 'keys', 'boosters']
        && EDITABLE_STORE_CATEGORIES === ['ranks', 'rubis', 'keys']
        && array_keys(public_routes()) === [
            'home',
            'ranks',
            'rubis',
            'keys',
            'boosters',
            'cart',
            'checkout',
            'login',
            'success',
            'privacy',
            'terms',
            'purchase-policy',
            'rules',
            'admin',
        ],
    'The active catalogue and its three editable categories remain fixed.'
);
$assert(
    in_array('categories', ADMIN_SECTIONS, true),
    'The administrator exposes the fixed category settings section.'
);
$throws(
    static fn () => validate_category_image_path('https://example.com/icon.png'),
    'Category images must remain inside the local assets folder.'
);
$assert(
    format_admin_datetime('2026-08-06 14:20:00') === '06/08/2026 14:20'
        && format_admin_coupon_discount([
            'discount_type' => 'percentage',
            'discount_value' => 15,
        ]) === '15%',
    'Administrator formatting helpers are available to page and AJAX templates.'
);

$_GET = [
    'search' => '  vip  ',
    'category' => 'ranks',
    'state' => 'inactive',
    'sort' => 'price_desc',
];

$productFilters = admin_product_filters();

$assert(
    $productFilters === [
        'search' => 'vip',
        'category' => 'ranks',
        'state' => 'inactive',
        'sort' => 'price_desc',
    ],
    'Valid product filters and sorting are normalized.'
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
    'sort' => 'invalid-sort',
];

$assert(
    admin_product_filters() === [
        'search' => '',
        'category' => '',
        'state' => '',
        'sort' => '',
    ],
    'Invalid product filters are discarded.'
);

$_GET = [];

$assert(
    admin_product_query_parameters([
        'search' => 'vip',
        'category' => '',
        'state' => 'active',
        'sort' => 'name_asc',
    ]) === [
        'search' => 'vip',
        'state' => 'active',
        'sort' => 'name_asc',
    ],
    'Product filter URLs omit empty values.'
);

$assert(
    admin_product_order_by(['sort' => 'price_desc']) === 'price_cents DESC, id DESC'
        && admin_product_order_by(['sort' => 'invalid']) === 'category ASC, sort_order ASC, id ASC',
    'Product sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'player' => ' Steve ',
    'status' => 'paid',
    'order' => 'abc',
    'date_from' => '2026-01-01',
    'date_to' => '2026-01-31',
    'sort' => 'total_desc',
    'page' => '2',
];

$orderFilters = admin_order_filters();

$assert(
    $orderFilters === [
        'player' => 'Steve',
        'status' => 'paid',
        'order' => 'abc',
        'date_from' => '2026-01-01',
        'date_to' => '2026-01-31',
        'sort' => 'total_desc',
        'page' => 2,
    ],
    'Valid order filters and sorting are normalized.'
);

$assert(
    admin_order_order_by($orderFilters) === 'total_cents DESC, id DESC'
        && admin_order_order_by(['sort' => 'invalid']) === 'created_at DESC, id DESC',
    'Order sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'search' => ' save ',
    'type' => 'percentage',
    'state' => 'available',
    'sort' => 'usage_desc',
];

$couponFilters = admin_coupon_filters();
$couponQuery = admin_coupons_query($couponFilters);

$assert(
    $couponFilters === [
        'search' => 'save',
        'type' => 'percentage',
        'state' => 'available',
        'sort' => 'usage_desc',
    ]
        && str_contains($couponQuery['where'], 'code LIKE :search')
        && str_contains($couponQuery['where'], 'discount_type = :discount_type')
        && str_contains($couponQuery['where'], 'used_count < max_uses')
        && $couponQuery['parameters']['search'] === '%SAVE%'
        && $couponQuery['parameters']['discount_type'] === 'percentage',
    'Coupon filters generate a parameterized query.'
);

$assert(
    admin_coupon_order_by($couponFilters) === 'used_count DESC, id DESC'
        && admin_coupon_order_by(['sort' => 'invalid']) === 'created_at DESC, id DESC',
    'Coupon sorting uses only server-side allowlisted clauses.'
);

$_GET = [
    'type' => 'invalid',
    'state' => 'invalid',
    'sort' => 'invalid',
];

$assert(
    admin_coupon_filters() === [
        'search' => '',
        'type' => '',
        'state' => '',
        'sort' => '',
    ],
    'Invalid coupon filters and sorting are discarded.'
);

$_GET = [];

$assert(
    is_file($root . '/public_html/ajax/admin-filter.php'),
    'The administrator AJAX filter endpoint exists.'
);

$adminJavaScript = file_get_contents(
    $root . '/public_html/js/admin.js'
);

$headerJavaScript = file_get_contents(
    $root . '/public_html/js/header.js'
);

$headerTemplate = file_get_contents(
    $root . '/includes/header.php'
);

$headerStyles = file_get_contents(
    $root . '/public_html/css/components.css'
);

$assert(
    is_string($headerJavaScript)
        && str_contains($headerJavaScript, 'data-smart-header')
        && str_contains($headerJavaScript, 'initialPrimaryOffset')
        && str_contains($headerJavaScript, 'is-hidden')
        && str_contains($headerJavaScript, 'is-fixed'),
    'The smart header script manages scroll direction, fixed state, and initial page offset.'
);

$assert(
    is_string($headerTemplate)
        && str_contains($headerTemplate, 'data-smart-header')
        && str_contains($headerTemplate, 'data-header-primary')
        && str_contains($headerTemplate, 'data-header-secondary'),
    'The public header exposes the hooks required by the smart header behavior.'
);

$assert(
    is_string($headerStyles)
        && str_contains($headerStyles, '.header-secondary.is-fixed')
        && str_contains($headerStyles, '.header-secondary.is-hidden'),
    'The shared header styles support fixed and hidden secondary header states.'
);


$assert(
    is_string($adminJavaScript)
        && str_contains(
            $adminJavaScript,
            'data-admin-filter-form'
        )
        && str_contains(
            $adminJavaScript,
            'data-admin-pagination'
        )
        && is_file($root . '/templates/admin/coupons-results.php'),
    'Administrator JavaScript supports AJAX filters and pagination.'
);

$loginJavaScript = file_get_contents($root . '/public_html/js/login.js');
$headerTemplate = file_get_contents($root . '/includes/header.php');
$assert(
    is_string($loginJavaScript)
        && str_contains($loginJavaScript, 'value === "bedrock"')
        && is_string($headerTemplate)
        && str_contains($headerTemplate, "assets/ip.png")
        && str_contains($headerTemplate, "app.bedrock_server_ip"),
    'Bedrock login behavior and dual server addresses are present.'
);

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
    $seedCategories = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
    $assert(
        array_diff($seedCategories, STORE_CATEGORIES) === [],
        'The seed creates products only for active catalogue categories.'
    );
    save_category_from_admin([
        'category' => 'ranks',
        'name' => 'Premium Ranks',
        'image' => 'assets/diamante.png',
    ]);
    $assert(
        store_category_name('ranks') === 'Premium Ranks'
            && store_category_image('ranks') === 'assets/diamante.png',
        'Administrators can update only the displayed category name and image.'
    );
    $throws(
        static fn () => save_category_from_admin([
            'category' => 'boosters',
            'name' => 'Boosters',
            'image' => 'assets/heart.png',
        ]),
        'Non-editable categories cannot be changed through the administrator.'
    );
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
    $expiredCoupons = all_coupons_admin([
        'search' => '',
        'type' => '',
        'state' => 'expired',
        'sort' => 'code_asc',
    ]);
    $exhaustedCoupons = all_coupons_admin([
        'search' => '',
        'type' => '',
        'state' => 'exhausted',
        'sort' => 'code_asc',
    ]);
    $assert(
        in_array('EXPIRED', array_column($expiredCoupons, 'code'), true),
        'Coupon AJAX filters can select expired coupons.'
    );
    $assert(
        in_array('EXHAUSTED', array_column($exhaustedCoupons, 'code'), true),
        'Coupon AJAX filters can select exhausted coupons.'
    );
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
