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

function verify_csrf(): void
{
    $submitted = request_string('csrf_token');
    $stored = $_SESSION['csrf_token'] ?? null;

    if (!is_string($stored) || $submitted === '' || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        exit(t('validation.csrf'));
    }
}

function random_token(int $bytes = 24): string
{
    return bin2hex(random_bytes($bytes));
}


function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https://mc-heads.net; connect-src 'self'");
    header('Cache-Control: private, no-store, max-age=0');
    header('Pragma: no-cache');

    if ((string) config('app.env', 'development') === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
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
        http_response_code(429);
        header('Retry-After: ' . $windowSeconds);
        exit(t('validation.rate_limit'));
    }

    $attempts[] = $now;
    $_SESSION['rate_limits'][$key] = $attempts;
}
