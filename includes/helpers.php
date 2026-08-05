<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function request_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    foreach (['/actions/', '/api/', '/ajax/'] as $marker) {
        $position = strpos($scriptName, $marker);

        if ($position !== false) {
            return rtrim(substr($scriptName, 0, $position), '/');
        }
    }

    $directory = str_replace('\\', '/', dirname($scriptName));

    return $directory === '/' ? '' : rtrim($directory, '/');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $configuredUrl = (string) config('app.url', '');

    if ($configuredUrl !== '') {
        return $path === '' ? $configuredUrl : $configuredUrl . '/' . $path;
    }

    $basePath = request_base_path();

    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

function redirect(string $path, int $status = 303): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function redirect_external(string $url, int $status = 303): never
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Invalid redirect URL.');
    }

    header('Location: ' . $url, true, $status);
    exit;
}

function current_request_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);

    return is_string($path) ? $path : '/';
}

function safe_return_path(?string $path, string $fallback = 'index.php'): string
{
    if ($path === null || $path === '') {
        return $fallback;
    }

    $decoded = rawurldecode($path);

    if (
        str_contains($decoded, '://')
        || str_starts_with($decoded, '//')
        || str_contains($decoded, "\r")
        || str_contains($decoded, "\n")
    ) {
        return $fallback;
    }

    $base = basename(parse_url($decoded, PHP_URL_PATH) ?: '');
    $allowed = [
        'index.php', 'ranks.php', 'rubis.php', 'keys.php', 'boosters.php',
        'battlepass.php', 'cart.php', 'checkout.php', 'login.php', 'success.php',
        'privacy.php', 'terms.php', 'purchase-policy.php', 'rules.php',
    ];

    return in_array($base, $allowed, true) ? $base : $fallback;
}

function require_get(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        exit('Method Not Allowed');
    }
}

function require_post(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method Not Allowed');
    }
}

function request_int(string $key, int $default = 0): int
{
    $raw = $_POST[$key] ?? null;

    if (!is_string($raw) && !is_int($raw)) {
        return $default;
    }

    $value = filter_var($raw, FILTER_VALIDATE_INT);

    return $value === false ? $default : (int) $value;
}

function request_string(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? null;

    return is_string($value) ? trim($value) : $default;
}

function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

function format_money(int $cents, ?string $currency = null): string
{
    $currency ??= (string) config('app.currency', 'EUR');
    $amount = number_format($cents / 100, 2, ',', '.');

    return $currency === 'EUR' ? $amount . ' €' : $amount . ' ' . $currency;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function now_sql(): string
{
    return date('Y-m-d H:i:s');
}


function parse_money_to_cents(string $value, string $fieldName = 'Amount'): int
{
    $normalized = str_replace(',', '.', trim($value));

    if (!preg_match('/^\d{1,7}(?:\.\d{1,2})?$/', $normalized)) {
        throw new InvalidArgumentException(t('validation.amount', ['field' => $fieldName]));
    }

    [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '');
    $decimal = str_pad($decimal, 2, '0');

    return ((int) $whole * 100) + (int) substr($decimal, 0, 2);
}

function parse_optional_datetime(string $value, string $fieldName = 'Date'): ?string
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);
    $errors = DateTimeImmutable::getLastErrors();

    if ($date === false || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        throw new InvalidArgumentException(t('validation.datetime', ['field' => $fieldName]));
    }

    return $date->format('Y-m-d H:i:s');
}


function public_error_message(Throwable $error, string $fallback): string
{
    return (bool) config('app.debug', false) ? $error->getMessage() : $fallback;
}
