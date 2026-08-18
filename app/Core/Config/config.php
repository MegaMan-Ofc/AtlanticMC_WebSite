<?php

declare(strict_types=1);

const BASE_PATH = __DIR__ . '/../../..';
const TEMPLATE_PATH = BASE_PATH . '/templates';

function parse_environment_line(string $line): ?array
{
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        return null;
    }

    [$name, $rawValue] = explode('=', $line, 2);
    $name = trim($name);
    $rawValue = ltrim($rawValue);

    if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
        return null;
    }

    if ($rawValue === '') {
        return [$name, ''];
    }

    $quote = $rawValue[0];

    if ($quote === '"' || $quote === "'") {
        $length = strlen($rawValue);
        $closing = null;

        for ($index = 1; $index < $length; $index++) {
            if ($rawValue[$index] === $quote && $rawValue[$index - 1] !== '\\') {
                $closing = $index;
                break;
            }
        }

        if ($closing === null) {
            return null;
        }

        $tail = trim(substr($rawValue, $closing + 1));

        if ($tail !== '' && !str_starts_with($tail, '#')) {
            return null;
        }

        $value = substr($rawValue, 1, $closing - 1);

        if ($quote === '"') {
            $value = str_replace(['\\n', '\\r', '\\t', '\\"', '\\\\'], ["\n", "\r", "\t", '"', '\\'], $value);
        }

        return [$name, $value];
    }

    $value = preg_replace('/\s+#.*$/', '', $rawValue);

    return [$name, trim(is_string($value) ? $value : $rawValue)];
}

function load_environment_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $parsed = parse_environment_line($line);

        if ($parsed === null) {
            continue;
        }

        [$name, $value] = $parsed;

        if (getenv($name) !== false) {
            continue;
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

load_environment_file(BASE_PATH . '/.env');

function resolve_application_path(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return BASE_PATH;
    }

    $windowsAbsolute = strlen($path) >= 3
        && ctype_alpha($path[0])
        && $path[1] === ':'
        && ($path[2] === '\\' || $path[2] === '/');

    if (str_starts_with($path, '/') || $windowsAbsolute) {
        return $path;
    }

    return BASE_PATH . '/' . ltrim($path, '/\\');
}

function env_value(string $name, mixed $default = null): mixed
{
    $value = getenv($name);

    if ($value === false) {
        return $default;
    }

    $normalized = strtolower(trim($value));

    return match ($normalized) {
        'true', '(true)', 'yes', 'on' => true,
        'false', '(false)', 'no', 'off' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

function env_csv(string $name): array
{
    return array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env_value($name, ''))
    ), static fn (string $value): bool => $value !== ''));
}

function config(?string $key = null, mixed $default = null): mixed
{
    static $configuration = null;

    if ($configuration === null) {
        $dbDriver = strtolower((string) env_value('DB_DRIVER', 'sqlite'));
        $sqlitePath = resolve_application_path(
            (string) env_value('DB_PATH', 'storage/store.sqlite')
        );
        $serverIp = trim((string) env_value('SERVER_IP', 'play.atlanticeu.online'));
        $bedrockServerIp = trim((string) env_value('BEDROCK_SERVER_IP', ''));

        if ($bedrockServerIp === '') {
            $bedrockServerIp = $serverIp;
        }

        $configuration = [
            'app' => [
                'name' => (string) env_value('APP_NAME', 'Atlantic SMP'),
                'env' => strtolower((string) env_value('APP_ENV', 'development')),
                'key' => (string) env_value('APP_KEY', ''),
                'url' => rtrim((string) env_value('APP_URL', ''), '/'),
                'timezone' => (string) env_value('APP_TIMEZONE', 'Europe/Lisbon'),
                'default_language' => (string) env_value('APP_DEFAULT_LANGUAGE', 'pt'),
                'debug' => (bool) env_value('APP_DEBUG', false),
                'force_https' => (bool) env_value('APP_FORCE_HTTPS', false),
                'trusted_proxies' => env_csv('TRUSTED_PROXIES'),
                'payments_enabled' => (bool) env_value('PAYMENTS_ENABLED', false),
                'server_ip' => $serverIp,
                'bedrock_server_ip' => $bedrockServerIp,
                'bedrock_server_port' => (int) env_value('BEDROCK_SERVER_PORT', '19132'),
                'bedrock_username_prefix' => (string) env_value('BEDROCK_USERNAME_PREFIX', ''),
                'discord_url' => (string) env_value('DISCORD_URL', 'https://discord.gg/atlanticnetwork'),
                'support_email' => (string) env_value('SUPPORT_EMAIL', 'support@atlantic.net'),
                'currency' => strtoupper((string) env_value('STORE_CURRENCY', 'EUR')),
                'vat_rate' => (float) env_value('VAT_RATE', '0.23'),
                'max_cart_quantity' => max(1, (int) env_value('MAX_CART_QUANTITY', '20')),
                'allow_test_orders' => (bool) env_value('ALLOW_TEST_ORDERS', false),
            ],
            'legal' => [
                'operator_name' => (string) env_value('LEGAL_OPERATOR_NAME', env_value('APP_NAME', 'Atlantic SMP')),
                'operator_address' => (string) env_value('LEGAL_OPERATOR_ADDRESS', ''),
                'operator_tax_id' => (string) env_value('LEGAL_OPERATOR_TAX_ID', ''),
                'country' => (string) env_value('LEGAL_COUNTRY', 'Portugal'),
                'privacy_email' => (string) env_value('PRIVACY_EMAIL', env_value('SUPPORT_EMAIL', 'support@atlantic.net')),
                'last_updated' => (string) env_value('LEGAL_LAST_UPDATED', '2026-08-05'),
            ],
            'database' => [
                'driver' => $dbDriver,
                'sqlite_path' => $sqlitePath,
                'host' => (string) env_value('DB_HOST', '127.0.0.1'),
                'port' => (int) env_value('DB_PORT', '3306'),
                'name' => (string) env_value('DB_NAME', 'atlantic_store'),
                'user' => (string) env_value('DB_USER', 'root'),
                'password' => (string) env_value('DB_PASSWORD', ''),
                'charset' => (string) env_value('DB_CHARSET', 'utf8mb4'),
                'connect_timeout' => max(1, (int) env_value('DB_CONNECT_TIMEOUT', '5')),
            ],
            'tebex' => [
                'public_token' => (string) env_value('TEBEX_PUBLIC_TOKEN', ''),
                'webhook_secret' => (string) env_value('TEBEX_WEBHOOK_SECRET', ''),
                'verify_webhook_amount' => (bool) env_value('TEBEX_VERIFY_WEBHOOK_AMOUNT', true),
                'coupons_enabled' => (bool) env_value('TEBEX_COUPONS_ENABLED', false),
                'allowed_webhook_ips' => env_csv('TEBEX_ALLOWED_WEBHOOK_IPS'),
            ],
            'logging' => [
                'app_path' => resolve_application_path((string) env_value('APP_LOG_PATH', 'storage/app.log')),
                'security_path' => resolve_application_path((string) env_value('SECURITY_LOG_PATH', 'storage/security.log')),
            ],
            'maintenance' => [
                'state_path' => resolve_application_path((string) env_value('MAINTENANCE_STATE_PATH', 'storage/maintenance.json')),
            ],
            'admin' => [
                'username' => (string) env_value('ADMIN_USERNAME', ''),
                'password_hash' => (string) env_value('ADMIN_PASSWORD_HASH', ''),
                'session_timeout' => max(300, (int) env_value('ADMIN_SESSION_TIMEOUT', '1800')),
                'login_max_attempts' => max(1, (int) env_value('ADMIN_LOGIN_MAX_ATTEMPTS', '5')),
                'login_window' => max(60, (int) env_value('ADMIN_LOGIN_WINDOW_SECONDS', '900')),
            ],
        ];

        if (!@date_default_timezone_set($configuration['app']['timezone'])) {
            throw new RuntimeException('Invalid APP_TIMEZONE configuration.');
        }
    }

    if ($key === null) {
        return $configuration;
    }

    $value = $configuration;

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function is_production(): bool
{
    return config('app.env') === 'production';
}

function configuration_errors(): array
{
    $errors = [];
    $environment = (string) config('app.env');
    $driver = (string) config('database.driver');
    $currency = (string) config('app.currency');

    if (!in_array($environment, ['development', 'test', 'production'], true)) {
        $errors[] = 'APP_ENV must be development, test or production.';
    }

    if (!in_array($driver, ['sqlite', 'mysql'], true)) {
        $errors[] = 'DB_DRIVER must be sqlite or mysql.';
    }

    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        $errors[] = 'STORE_CURRENCY must contain three uppercase letters.';
    }

    if ((int) config('database.port') < 1 || (int) config('database.port') > 65535) {
        $errors[] = 'DB_PORT is invalid.';
    }

    if (trim((string) config('app.server_ip')) === '') {
        $errors[] = 'SERVER_IP is required.';
    }

    if (trim((string) config('app.bedrock_server_ip')) === '') {
        $errors[] = 'BEDROCK_SERVER_IP is required.';
    }

    if ((int) config('app.bedrock_server_port') < 1 || (int) config('app.bedrock_server_port') > 65535) {
        $errors[] = 'BEDROCK_SERVER_PORT is invalid.';
    }

    if (!is_production()) {
        return $errors;
    }

    $appUrl = (string) config('app.url');
    $urlParts = parse_url($appUrl);

    if (!is_array($urlParts) || ($urlParts['scheme'] ?? '') !== 'https' || empty($urlParts['host'])) {
        $errors[] = 'APP_URL must be a complete HTTPS URL in production.';
    }

    if ((bool) config('app.debug')) {
        $errors[] = 'APP_DEBUG must be false in production.';
    }

    if (!(bool) config('app.force_https')) {
        $errors[] = 'APP_FORCE_HTTPS must be true in production.';
    }

    if ((bool) config('app.allow_test_orders')) {
        $errors[] = 'ALLOW_TEST_ORDERS must be false in production.';
    }

    if ((bool) env_value('APP_AUTO_MIGRATE', false)) {
        $errors[] = 'APP_AUTO_MIGRATE must be false or removed in production.';
    }

    if (strlen((string) config('app.key')) < 32) {
        $errors[] = 'APP_KEY must contain at least 32 characters in production.';
    }

    if ($driver !== 'mysql') {
        $errors[] = 'DB_DRIVER must be mysql in production.';
    }

    foreach (['host', 'name', 'user', 'password'] as $key) {
        if (trim((string) config('database.' . $key)) === '') {
            $errors[] = 'Database configuration is incomplete.';
            break;
        }
    }

    if ((string) config('database.charset') !== 'utf8mb4') {
        $errors[] = 'DB_CHARSET must be utf8mb4 in production.';
    }

    $publicPath = realpath(BASE_PATH . '/public_html') ?: BASE_PATH . '/public_html';

    foreach (['app_path', 'security_path'] as $logKey) {
        $logPath = (string) config('logging.' . $logKey);
        $normalizedLogPath = str_replace('\\', '/', $logPath);
        $normalizedPublicPath = rtrim(str_replace('\\', '/', $publicPath), '/');

        if ($normalizedLogPath === $normalizedPublicPath || str_starts_with($normalizedLogPath, $normalizedPublicPath . '/')) {
            $errors[] = 'Log files must be stored outside public_html/.';
        }
    }

    $adminUsername = trim((string) config('admin.username'));
    $adminHash = trim((string) config('admin.password_hash'));
    $passwordInfo = $adminHash === '' ? ['algoName' => 'unknown'] : password_get_info($adminHash);

    if ($adminUsername === '' || ($passwordInfo['algoName'] ?? 'unknown') === 'unknown') {
        $errors[] = 'Administrator credentials are not configured correctly.';
    }

    $webhookSecret = trim((string) config('tebex.webhook_secret'));

    if ($webhookSecret !== '' && strlen($webhookSecret) < 16) {
        $errors[] = 'TEBEX_WEBHOOK_SECRET must contain at least 16 characters when configured.';
    }

    if ((bool) config('app.payments_enabled')) {
        if (trim((string) config('tebex.public_token')) === '') {
            $errors[] = 'TEBEX_PUBLIC_TOKEN is required when payments are enabled.';
        }

        if ($webhookSecret === '') {
            $errors[] = 'TEBEX_WEBHOOK_SECRET is required when payments are enabled.';
        }

        if (!(bool) config('tebex.verify_webhook_amount')) {
            $errors[] = 'TEBEX_VERIFY_WEBHOOK_AMOUNT must be true when payments are enabled.';
        }
    }

    return array_values(array_unique($errors));
}

function validate_runtime_configuration(): void
{
    $errors = configuration_errors();

    if ($errors !== []) {
        throw new RuntimeException(implode(' ', $errors));
    }
}
