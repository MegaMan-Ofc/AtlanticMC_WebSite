<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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


function query_int(string $key, int $default = 0): int
{
    $raw = $_GET[$key] ?? null;

    if (!is_string($raw) && !is_int($raw)) {
        return $default;
    }

    $value = filter_var($raw, FILTER_VALIDATE_INT);

    return $value === false ? $default : (int) $value;
}

function query_string(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? null;

    return is_string($value) ? trim($value) : $default;
}

function ip_matches_network(string $ip, string $network): bool
{
    $network = trim($network);

    if ($network === '') {
        return false;
    }

    if (!str_contains($network, '/')) {
        return hash_equals($network, $ip);
    }

    [$subnet, $prefix] = array_pad(explode('/', $network, 2), 2, '');
    $ipBinary = @inet_pton($ip);
    $subnetBinary = @inet_pton($subnet);

    if ($ipBinary === false || $subnetBinary === false || strlen($ipBinary) !== strlen($subnetBinary)) {
        return false;
    }

    $prefixLength = filter_var($prefix, FILTER_VALIDATE_INT);
    $maximum = strlen($ipBinary) * 8;

    if ($prefixLength === false || $prefixLength < 0 || $prefixLength > $maximum) {
        return false;
    }

    $fullBytes = intdiv((int) $prefixLength, 8);
    $remainingBits = (int) $prefixLength % 8;

    if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($subnetBinary, 0, $fullBytes)) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

    return (ord($ipBinary[$fullBytes]) & $mask) === (ord($subnetBinary[$fullBytes]) & $mask);
}

function request_from_trusted_proxy(): bool
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $trusted = config('app.trusted_proxies', []);

    if (!filter_var($remote, FILTER_VALIDATE_IP) || !is_array($trusted)) {
        return false;
    }

    foreach ($trusted as $network) {
        if (is_string($network) && ip_matches_network($remote, $network)) {
            return true;
        }
    }

    return false;
}

function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

    if (!filter_var($remote, FILTER_VALIDATE_IP)) {
        $remote = '127.0.0.1';
    }

    if (!request_from_trusted_proxy()) {
        return $remote;
    }

    $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    $addresses = array_values(array_filter(array_map('trim', explode(',', $forwarded))));
    $addresses[] = $remote;
    $trusted = config('app.trusted_proxies', []);

    for ($index = count($addresses) - 1; $index >= 0; $index--) {
        $candidate = $addresses[$index];

        if (!filter_var($candidate, FILTER_VALIDATE_IP)) {
            continue;
        }

        $isTrusted = false;

        foreach ($trusted as $network) {
            if (is_string($network) && ip_matches_network($candidate, $network)) {
                $isTrusted = true;
                break;
            }
        }

        if (!$isTrusted) {
            return $candidate;
        }
    }

    return $remote;
}

function request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if (!request_from_trusted_proxy()) {
        return false;
    }

    return strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0] ?? '')) === 'https';
}

function enforce_https_request(): void
{
    if (!(bool) config('app.force_https', false) || request_is_https()) {
        return;
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(400);
        exit('HTTPS is required.');
    }

    $appUrl = (string) config('app.url');
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $appUrl . '/' . ltrim($requestUri, '/'), true, 308);
    exit;
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
