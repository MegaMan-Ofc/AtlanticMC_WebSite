<?php

declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';

function load_environment_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));

        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        if (
            strlen($value) >= 2
            && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

load_environment_file(BASE_PATH . '/.env');

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

function config(?string $key = null, mixed $default = null): mixed
{
    static $configuration = null;

    if ($configuration === null) {
        $dbDriver = strtolower((string) env_value('DB_DRIVER', 'sqlite'));
        $sqlitePath = (string) env_value('DB_PATH', BASE_PATH . '/storage/store.sqlite');

        if (!str_starts_with($sqlitePath, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $sqlitePath)) {
            $sqlitePath = BASE_PATH . '/' . ltrim($sqlitePath, '/\\');
        }

        $configuration = [
            'app' => [
                'name' => (string) env_value('APP_NAME', 'Atlantic Anarchy'),
                'env' => (string) env_value('APP_ENV', 'development'),
                'url' => rtrim((string) env_value('APP_URL', ''), '/'),
                'timezone' => (string) env_value('APP_TIMEZONE', 'Europe/Lisbon'),
                'default_language' => (string) env_value('APP_DEFAULT_LANGUAGE', 'pt'),
                'auto_migrate' => (bool) env_value('APP_AUTO_MIGRATE', true),
                'debug' => (bool) env_value('APP_DEBUG', false),
                'server_ip' => (string) env_value('SERVER_IP', 'atlanticmc.secure.pebble.host'),
                'discord_url' => (string) env_value('DISCORD_URL', 'https://discord.gg/atlanticnetwork'),
                'support_email' => (string) env_value('SUPPORT_EMAIL', 'support@atlantic.net'),
                'currency' => strtoupper((string) env_value('STORE_CURRENCY', 'EUR')),
                'vat_rate' => (float) env_value('VAT_RATE', '0.23'),
                'max_cart_quantity' => max(1, (int) env_value('MAX_CART_QUANTITY', '20')),
                'allow_test_orders' => (bool) env_value('ALLOW_TEST_ORDERS', false),
            ],
            'legal' => [
                'operator_name' => (string) env_value('LEGAL_OPERATOR_NAME', env_value('APP_NAME', 'Atlantic Anarchy')),
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
            ],
            'tebex' => [
                'public_token' => (string) env_value('TEBEX_PUBLIC_TOKEN', ''),
                'webhook_secret' => (string) env_value('TEBEX_WEBHOOK_SECRET', ''),
                'verify_webhook_amount' => (bool) env_value('TEBEX_VERIFY_WEBHOOK_AMOUNT', true),
                'allowed_webhook_ips' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) env_value('TEBEX_ALLOWED_WEBHOOK_IPS', ''))
                ))),
            ],
            'admin' => [
                'username' => (string) env_value('ADMIN_USERNAME', 'admin'),
                'password_hash' => (string) env_value('ADMIN_PASSWORD_HASH', ''),
            ],
        ];

        date_default_timezone_set($configuration['app']['timezone']);
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
