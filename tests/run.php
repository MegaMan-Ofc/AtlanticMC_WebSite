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
require_once $root . '/includes/media.php';
require_once $root . '/includes/admin_categories.php';
require_once $root . '/includes/admin_products.php';
require_once $root . '/includes/admin_coupons.php';
require_once $root . '/includes/admin_orders.php';
require_once $root . '/includes/admin_dashboard.php';
require_once $root . '/includes/minecraft_recipient.php';
require_once $root . '/includes/tebex.php';
require_once $root . '/includes/orders.php';

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

$webhookBody = json_encode([
    'id' => 'evt-test',
    'type' => 'validation.webhook',
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$webhookSignature = hash_hmac(
    'sha256',
    hash('sha256', $webhookBody),
    'test-webhook-secret-0123456789'
);
$assert(
    tebex_webhook_is_configured()
        && !tebex_is_configured(),
    'Tebex webhooks can be configured while customer payments remain disabled.'
);
$assert(
    verify_tebex_webhook_signature($webhookBody, $webhookSignature)
        && !verify_tebex_webhook_signature($webhookBody . 'x', $webhookSignature),
    'Tebex webhook signatures are verified against the exact request body.'
);
$assert(
    tebex_money_to_cents('1.38') === 138
        && tebex_money_to_cents(5) === 500
        && tebex_money_to_cents('invalid') === null,
    'Tebex monetary values are normalized safely to integer cents.'
);
$assert(
    tebex_basket_totals([
        'total_price' => 12.34,
        'currency' => 'eur',
    ]) === [
        'total_cents' => 1234,
        'currency' => 'EUR',
    ],
    'Tebex basket totals preserve the provider-calculated total and currency.'
);
$tebexOrderFixture = [
    'total_cents' => 1000,
    'currency' => 'EUR',
    'tebex_total_cents' => 1234,
    'tebex_currency' => 'EUR',
    'items' => [
        ['tebex_package_id' => '100', 'quantity' => 1],
        ['tebex_package_id' => '200', 'quantity' => 2],
    ],
];
$tebexSubjectFixture = [
    'products' => [
        ['id' => 200, 'quantity' => 2],
        ['id' => 100, 'quantity' => 1],
    ],
    'price_paid' => [
        'amount' => '12.34',
        'currency' => 'EUR',
    ],
];
$assert(
    tebex_webhook_matches_order($tebexSubjectFixture, $tebexOrderFixture),
    'Completed Tebex payments must match package IDs, quantities, provider total and currency.'
);
$wrongQuantitySubject = $tebexSubjectFixture;
$wrongQuantitySubject['products'][0]['quantity'] = 1;
$assert(
    !tebex_webhook_matches_order($wrongQuantitySubject, $tebexOrderFixture),
    'Tebex webhook validation rejects incorrect package quantities.'
);
$wrongAmountSubject = $tebexSubjectFixture;
$wrongAmountSubject['price_paid']['amount'] = '12.35';
$assert(
    !tebex_webhook_matches_order($wrongAmountSubject, $tebexOrderFixture),
    'Tebex webhook validation rejects an incorrect provider total.'
);
$wrongCurrencySubject = $tebexSubjectFixture;
$wrongCurrencySubject['price_paid']['currency'] = 'USD';
$assert(
    !tebex_webhook_matches_order($wrongCurrencySubject, $tebexOrderFixture),
    'Tebex webhook validation rejects an incorrect provider currency.'
);

$assert(
    public_route_name_from_request_uri('/') === 'home'
        && public_route_name_from_request_uri('/admin') === 'admin'
        && public_route_name_from_request_uri('/purchase-policy?from=footer') === 'purchase-policy'
        && public_route_name_from_request_uri('/does-not-exist') === null,
    'Clean request paths resolve through the shared public route table.'
);

$assert(
    public_category_slug_from_request_uri('/custom-kits') === 'custom-kits'
        && public_category_slug_from_request_uri('/cart') === null
        && category_path('custom-kits') === 'custom-kits'
        && safe_return_path('/custom-kits?source=store', '') === 'custom-kits?source=store',
    'Dynamic category slugs resolve to clean public paths without colliding with reserved routes.'
);
$throws(
    static fn () => validate_category_slug('cart'),
    'Category slugs cannot use reserved public routes.'
);

$assert(
    array_keys(public_routes()) === [
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
    'The shared public route table remains stable while catalogue categories become dynamic.'
);
$assert(
    in_array('categories', ADMIN_SECTIONS, true)
        && is_file($root . '/database/migrations/sqlite/004_dynamic_categories.php')
        && is_file($root . '/database/migrations/mysql/004_dynamic_categories.php')
        && is_file($root . '/public_html/actions/admin_delete_category.php')
        && is_file($root . '/public_html/category.php')
        && is_file($root . '/controllers/category.php')
        && is_file($root . '/controllers/catalog.php'),
    'Dynamic category administration, migrations and public storefront routing are present.'
);
$throws(
    static fn () => validate_category_image_path('https://example.com/icon.png'),
    'Category images reject external paths.'
);
$assert(
    is_managed_upload_path('uploads/categories/category-' . str_repeat('a', 32) . '.png')
        && is_managed_upload_path('uploads/products/product-' . str_repeat('b', 32) . '.png')
        && !is_managed_upload_path('uploads/categories/file.php'),
    'Managed media paths only accept generated PNG locations.'
);

$testPngPath = $root . '/storage/test-upload.png';
$testTextPath = $root . '/storage/test-upload.txt';
file_put_contents(
    $testPngPath,
    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
);
file_put_contents($testTextPath, 'not a png');

try {
    validate_png_file($testPngPath, (int) filesize($testPngPath));
    $assert(true, 'Valid PNG uploads pass content validation.');
} catch (Throwable) {
    $assert(false, 'Valid PNG uploads pass content validation.');
}

$throws(
    static fn () => validate_png_file($testTextPath, (int) filesize($testTextPath)),
    'Non-PNG upload content is rejected.'
);
@unlink($testPngPath);
@unlink($testTextPath);
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
    'category_id' => '2',
    'state' => 'inactive',
    'sort' => 'price_desc',
];

$productFilters = admin_product_filters();

$assert(
    $productFilters === [
        'search' => 'vip',
        'category_id' => 2,
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
            'p.category_id = :category_id'
        )
        && str_contains(
            $productQuery['where'],
            'active = :active'
        )
        && $productQuery['parameters'] === [
            'search_name' => '%vip%',
            'search_slug' => '%vip%',
            'search_tebex' => '%vip%',
            'category_id' => 2,
            'active' => 0,
        ],
    'Product filters generate a parameterized query.'
);

$_GET = [
    'category_id' => '-4',
    'state' => 'invalid-state',
    'sort' => 'invalid-sort',
];

$assert(
    admin_product_filters() === [
        'search' => '',
        'category_id' => 0,
        'state' => '',
        'sort' => '',
    ],
    'Invalid product filters are discarded.'
);

$_GET = [];

$assert(
    admin_product_query_parameters([
        'search' => 'vip',
        'category_id' => 0,
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
    admin_product_order_by(['sort' => 'price_desc']) === 'p.price_cents DESC, p.id DESC'
        && admin_product_order_by(['sort' => 'invalid']) === 'c.sort_order ASC, c.id ASC, p.sort_order ASC, p.id ASC',
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
        && str_contains($adminJavaScript, 'data-admin-image-input')
        && str_contains($adminJavaScript, 'data-admin-slug-source')
        && is_file($root . '/templates/admin/coupons-results.php'),
    'Administrator JavaScript supports AJAX filters and pagination.'
);

$categoryDialogTemplate = file_get_contents($root . '/templates/admin/category-dialog.php');
$productDialogTemplate = file_get_contents($root . '/templates/admin/product-dialog.php');
$uploadProtection = file_get_contents($root . '/public_html/uploads/.htaccess');
$publicHtaccess = file_get_contents($root . '/public_html/.htaccess');
$assert(
    is_string($categoryDialogTemplate)
        && str_contains($categoryDialogTemplate, 'accept="image/png,.png"')
        && str_contains($categoryDialogTemplate, 'admin_delete_category.php')
        && is_string($productDialogTemplate)
        && str_contains($productDialogTemplate, 'accept="image/png,.png"')
        && is_string($uploadProtection)
        && str_contains($uploadProtection, 'Require all denied')
        && is_string($publicHtaccess)
        && str_contains($publicHtaccess, 'LimitRequestBody 6291456'),
    'Category and product forms use protected PNG uploads with a bounded request size.'
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

    $assert((int) $pdo->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn() >= 5, 'All SQLite migrations are recorded.');
    $orderColumns = array_column($pdo->query('PRAGMA table_info(orders)')->fetchAll(), 'name');
    $assert(
        in_array('tebex_total_cents', $orderColumns, true)
            && in_array('tebex_currency', $orderColumns, true),
        'The Tebex hardening migration stores provider basket totals on orders.'
    );
    $assert((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() > 0, 'The seed creates products.');
    $assert((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() >= 4, 'The dynamic category migration creates catalogue categories.');
    $assert((int) $pdo->query('SELECT COUNT(*) FROM products WHERE category_id IS NULL')->fetchColumn() === 0, 'Seed products are linked to dynamic category IDs.');

    $ranks = store_category_by_slug('ranks', true);
    $assert(is_array($ranks), 'Existing category slugs are migrated to category records.');
    save_category_from_admin([
        'id' => (int) $ranks['id'],
        'slug' => 'ranks',
        'name' => 'Premium Ranks',
        'sort_order' => 10,
        'active' => '1',
    ], 'assets/diamante.png');
    $assert(
        store_category_name('ranks') === 'Premium Ranks'
            && store_category_image('ranks') === 'assets/diamante.png',
        'Administrators can edit dynamic category data.'
    );

    $testCategoryId = save_category_from_admin([
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
        'active' => '1',
    ], 'assets/diamante.png');
    $assert(store_category_by_id($testCategoryId, true) !== null, 'Administrators can create categories.');
    $homeCategorySlugs = array_column(home_store_categories(), 'slug');
    $assert(
        in_array('test-category', $homeCategorySlugs, true)
            && category_configuration('test-category')['heading'] === 'Test Category',
        'Active dynamic categories appear on the homepage and receive a generic catalogue page configuration.'
    );

    $testProductId = save_product_from_admin([
        'category_id' => $testCategoryId,
        'name' => 'Dynamic Product',
        'slug' => 'dynamic-product',
        'description' => 'Dynamic category product.',
        'price' => '4.99',
        'sort_order' => 10,
        'active' => '1',
        'tebex_package_id' => '',
    ]);
    $testProduct = product_by_id($testProductId, true);
    $assert(
        is_array($testProduct)
            && (int) $testProduct['category_id'] === $testCategoryId
            && (string) $testProduct['category'] === 'test-category',
        'Products are linked to categories by ID while preserving the category slug mirror.'
    );
    $throws(
        static fn () => delete_category_from_admin($testCategoryId),
        'Categories containing products cannot be deleted.'
    );
    save_category_from_admin([
        'id' => $testCategoryId,
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
    ]);
    $assert(
        !in_array('test-category', array_column(home_store_categories(), 'slug'), true)
            && product_by_id($testProductId) === null,
        'Inactive categories disappear from the public homepage and hide their products.'
    );
    save_category_from_admin([
        'id' => $testCategoryId,
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
        'active' => '1',
    ]);
    delete_product_from_admin($testProductId);
    delete_category_from_admin($testCategoryId);
    $assert(store_category_by_id($testCategoryId, true) === null, 'Empty categories can be deleted.');
    $throws(
        static fn () => delete_category_from_admin((int) $ranks['id']),
        'Seed categories containing products cannot be deleted.'
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
