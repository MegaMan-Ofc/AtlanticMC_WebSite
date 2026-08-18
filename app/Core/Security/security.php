<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(string $submitted, mixed $stored): bool
{
    return is_string($stored)
        && $submitted !== ''
        && hash_equals($stored, $submitted);
}

function verify_csrf(): void
{
    $submitted = request_string('csrf_token');
    $stored = $_SESSION['csrf_token'] ?? null;

    if (!csrf_is_valid($submitted, $stored)) {
        security_log('warning', 'csrf_validation_failed', ['ip_hash' => hash('sha256', client_ip())]);
        http_response_code(419);
        exit(t('validation.csrf'));
    }
}

function random_token(int $bytes = 24): string
{
    return bin2hex(random_bytes($bytes));
}

function request_is_sensitive(): bool
{
    $route = current_route_name();
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    return in_array($route, ['admin', 'checkout', 'success'], true)
        || str_contains($script, '/actions/admin_')
        || str_contains($script, '/ajax/')
        || str_contains($script, '/api/');
}

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; form-action 'self'; frame-ancestors 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https://mc-heads.net; connect-src 'self'");

    if (request_is_sensitive()) {
        header('Cache-Control: no-store, max-age=0');
        header('Pragma: no-cache');
    } else {
        header('Cache-Control: private, no-cache, max-age=0, must-revalidate');
    }

    if (is_production() && request_is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

function enforce_rate_limit(string $key, int $maximumAttempts, int $windowSeconds): void
{
    $now = time();
    $bucket = $_SESSION['rate_limits'][$key] ?? [];
    $attempts = is_array($bucket)
        ? array_values(array_filter($bucket, static fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp > ($now - $windowSeconds)))
        : [];

    if (count($attempts) >= $maximumAttempts) {
        security_log('warning', 'session_rate_limit_exceeded', ['key' => $key, 'ip_hash' => hash('sha256', client_ip())]);
        http_response_code(429);
        header('Retry-After: ' . $windowSeconds);
        exit(t('validation.rate_limit'));
    }

    $attempts[] = $now;
    $_SESSION['rate_limits'][$key] = $attempts;
}
