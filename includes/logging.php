<?php

declare(strict_types=1);

function request_id(): string
{
    static $requestId = null;

    if (is_string($requestId)) {
        return $requestId;
    }

    $incoming = trim((string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? ''));
    $requestId = preg_match('/^[A-Za-z0-9._-]{8,80}$/', $incoming) === 1
        ? $incoming
        : bin2hex(random_bytes(16));

    return $requestId;
}

function log_path(string $channel): string
{
    return match ($channel) {
        'security' => (string) config('logging.security_path'),
        default => (string) config('logging.app_path'),
    };
}


function redact_log_string(string $value): string
{
    $secrets = [
        (string) config('app.key', ''),
        (string) config('database.password', ''),
        (string) config('tebex.public_token', ''),
        (string) config('tebex.webhook_secret', ''),
        (string) config('admin.password_hash', ''),
    ];

    foreach ($secrets as $secret) {
        if ($secret !== '') {
            $value = str_replace($secret, '[redacted]', $value);
        }
    }

    return preg_replace('/\b[a-f0-9]{40,}\b/i', '[redacted-token]', $value) ?? $value;
}

function sanitize_log_value(mixed $value, ?string $key = null): mixed
{
    $sensitive = [
        'password',
        'password_hash',
        'secret',
        'token',
        'authorization',
        'cookie',
        'session',
        'payload',
        'raw_body',
    ];
    $normalizedKey = strtolower((string) $key);

    foreach ($sensitive as $needle) {
        if ($normalizedKey !== '' && str_contains($normalizedKey, $needle)) {
            return '[redacted]';
        }
    }

    if (is_array($value)) {
        $sanitized = [];

        foreach ($value as $childKey => $childValue) {
            $sanitized[(string) $childKey] = sanitize_log_value($childValue, (string) $childKey);
        }

        return $sanitized;
    }

    if (is_object($value)) {
        return '[object ' . $value::class . ']';
    }

    if (is_string($value)) {
        $redacted = redact_log_string($value);

        return function_exists('mb_strcut')
            ? mb_strcut($redacted, 0, 1000, 'UTF-8')
            : substr($redacted, 0, 1000);
    }

    if (is_scalar($value) || $value === null) {
        return $value;
    }

    return '[unsupported]';
}

function write_log_record(string $channel, string $level, string $event, array $context = []): void
{
    $path = log_path($channel);
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        error_log('AtlanticStore logging directory is unavailable.');
        return;
    }

    $record = [
        'timestamp' => gmdate('c'),
        'level' => strtolower($level),
        'channel' => $channel,
        'event' => $event,
        'request_id' => request_id(),
        'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
        'path' => (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? ''),
        'context' => sanitize_log_value($context),
    ];
    $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if (!is_string($encoded)) {
        error_log('AtlanticStore could not encode log event ' . $event . '.');
        return;
    }

    $line = $encoded . PHP_EOL;
    $handle = @fopen($path, 'ab');

    if ($handle !== false) {
        @chmod($path, 0640);
    }

    if ($handle === false) {
        error_log('AtlanticStore log file is unavailable for event ' . $event . '.');
        return;
    }

    try {
        if (flock($handle, LOCK_EX)) {
            fwrite($handle, $line);
            fflush($handle);
            flock($handle, LOCK_UN);
        }
    } finally {
        fclose($handle);
    }
}

function app_log(string $level, string $event, array $context = []): void
{
    write_log_record('app', $level, $event, $context);
}

function security_log(string $level, string $event, array $context = []): void
{
    write_log_record('security', $level, $event, $context);
}

function log_exception(Throwable $error, string $event = 'unhandled_exception'): void
{
    $context = [
        'exception' => $error::class,
        'message' => $error->getMessage(),
        'code' => $error->getCode(),
    ];

    if (!is_production()) {
        $context['file'] = $error->getFile();
        $context['line'] = $error->getLine();
        $context['trace'] = $error->getTraceAsString();
    }

    app_log('error', $event, $context);
}
