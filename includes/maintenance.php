<?php

declare(strict_types=1);

const MAINTENANCE_CONFIRM_ENABLE = 'ATIVAR MANUTENCAO';
const MAINTENANCE_CONFIRM_DISABLE = 'ABRIR LOJA';
const MAINTENANCE_MESSAGE_MAX_LENGTH = 240;

function maintenance_state_path(): string
{
    return (string) config('maintenance.state_path', BASE_PATH . '/storage/maintenance.json');
}

function maintenance_default_state(): array
{
    return [
        'enabled' => false,
        'message' => '',
        'ends_at' => null,
        'updated_at' => null,
        'updated_by' => null,
        'integrity_ok' => true,
    ];
}

function maintenance_failed_state(): array
{
    return [
        'enabled' => true,
        'message' => '',
        'ends_at' => null,
        'updated_at' => null,
        'updated_by' => null,
        'integrity_ok' => false,
    ];
}

function maintenance_normalize_message(string $message): string
{
    $message = trim(preg_replace('/\s+/u', ' ', $message) ?? $message);

    if (function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > MAINTENANCE_MESSAGE_MAX_LENGTH) {
        throw new InvalidArgumentException(t('validation.maintenance_message_too_long'));
    }

    if (!function_exists('mb_strlen') && strlen($message) > MAINTENANCE_MESSAGE_MAX_LENGTH) {
        throw new InvalidArgumentException(t('validation.maintenance_message_too_long'));
    }

    return $message;
}

function maintenance_normalize_ends_at(string $value): ?string
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    try {
        $timezone = new DateTimeZone((string) config('app.timezone', 'Europe/Lisbon'));
        $date = new DateTimeImmutable($value, $timezone);
    } catch (Throwable) {
        throw new InvalidArgumentException(t('validation.maintenance_invalid_end'));
    }

    if ($date->getTimestamp() <= time() + 60) {
        throw new InvalidArgumentException(t('validation.maintenance_invalid_end'));
    }

    return $date->format('Y-m-d H:i:s');
}

function maintenance_normalize_state(array $state): array
{
    $normalized = maintenance_default_state();
    $normalized['enabled'] = ($state['enabled'] ?? false) === true;
    $normalized['message'] = is_string($state['message'] ?? null)
        ? trim((string) $state['message'])
        : '';
    $normalized['ends_at'] = is_string($state['ends_at'] ?? null) && trim((string) $state['ends_at']) !== ''
        ? trim((string) $state['ends_at'])
        : null;
    $normalized['updated_at'] = is_string($state['updated_at'] ?? null) && trim((string) $state['updated_at']) !== ''
        ? trim((string) $state['updated_at'])
        : null;
    $normalized['updated_by'] = is_string($state['updated_by'] ?? null) && trim((string) $state['updated_by']) !== ''
        ? trim((string) $state['updated_by'])
        : null;

    return $normalized;
}

function maintenance_state(): array
{
    $path = maintenance_state_path();

    if (!is_file($path)) {
        return maintenance_default_state();
    }

    $raw = @file_get_contents($path);

    if (!is_string($raw) || trim($raw) === '') {
        security_log('error', 'maintenance_state_unreadable');
        return maintenance_failed_state();
    }

    try {
        $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        security_log('error', 'maintenance_state_invalid', ['message' => $error->getMessage()]);
        return maintenance_failed_state();
    }

    if (!is_array($decoded) || !array_key_exists('enabled', $decoded) || !is_bool($decoded['enabled'])) {
        security_log('error', 'maintenance_state_invalid_shape');
        return maintenance_failed_state();
    }

    return maintenance_normalize_state($decoded);
}

function maintenance_is_enabled(?array $state = null): bool
{
    $state ??= maintenance_state();

    return ($state['enabled'] ?? false) === true;
}

function maintenance_confirmation_phrase(bool $enable): string
{
    return $enable ? MAINTENANCE_CONFIRM_ENABLE : MAINTENANCE_CONFIRM_DISABLE;
}

function maintenance_write_state(array $state): void
{
    $path = maintenance_state_path();
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException(t('messages.maintenance_state_write_failed'));
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(t('messages.maintenance_state_write_failed'));
    }

    $payload = [
        'enabled' => ($state['enabled'] ?? false) === true,
        'message' => (string) ($state['message'] ?? ''),
        'ends_at' => $state['ends_at'] ?? null,
        'updated_at' => $state['updated_at'] ?? null,
        'updated_by' => $state['updated_by'] ?? null,
    ];
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
    $temporary = $directory . '/maintenance-' . bin2hex(random_bytes(8)) . '.tmp';

    try {
        if (file_put_contents($temporary, $encoded, LOCK_EX) === false) {
            throw new RuntimeException(t('messages.maintenance_state_write_failed'));
        }

        @chmod($temporary, 0600);

        if (!@rename($temporary, $path)) {
            throw new RuntimeException(t('messages.maintenance_state_write_failed'));
        }

        @chmod($path, 0600);
    } finally {
        if (is_file($temporary)) {
            @unlink($temporary);
        }
    }
}

function maintenance_set_state(bool $enabled, string $message, string $endsAt, string $updatedBy): array
{
    $state = [
        'enabled' => $enabled,
        'message' => $enabled ? maintenance_normalize_message($message) : '',
        'ends_at' => $enabled ? maintenance_normalize_ends_at($endsAt) : null,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => trim($updatedBy) === '' ? null : trim($updatedBy),
    ];

    maintenance_write_state($state);

    return maintenance_normalize_state($state);
}

function maintenance_retry_after_seconds(?array $state = null): int
{
    $state ??= maintenance_state();
    $endsAt = $state['ends_at'] ?? null;

    if (is_string($endsAt) && $endsAt !== '') {
        $timestamp = strtotime($endsAt);

        if ($timestamp !== false && $timestamp > time()) {
            return max(60, min(86400, $timestamp - time()));
        }
    }

    return 1800;
}

function maintenance_request_is_allowed(): bool
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basename = basename($script);

    if ($basename === 'maintenance.php' || current_route_name() === 'admin') {
        return true;
    }

    return str_contains($script, '/actions/admin_')
        || str_contains($script, '/ajax/admin-')
        || str_ends_with($script, '/actions/language.php')
        || str_ends_with($script, '/api/tebex_webhook.php');
}

function render_maintenance_page(): never
{
    http_response_code(503);
    header('Retry-After: ' . maintenance_retry_after_seconds());
    header('Cache-Control: no-store, max-age=0');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow');

    if (!defined('ATLANTIC_MAINTENANCE_RENDERING')) {
        define('ATLANTIC_MAINTENANCE_RENDERING', true);
    }

    $basePath = request_base_path();
    $script = BASE_PATH . '/public_html/maintenance.php';

    $_SERVER['SCRIPT_FILENAME'] = $script;
    $_SERVER['SCRIPT_NAME'] = ($basePath === '' ? '' : $basePath) . '/maintenance.php';

    require $script;
    exit;
}

function enforce_maintenance_mode(): void
{
    $state = maintenance_state();

    if (!maintenance_is_enabled($state) || maintenance_request_is_allowed()) {
        return;
    }

    http_response_code(503);
    header('Retry-After: ' . maintenance_retry_after_seconds($state));
    header('Cache-Control: no-store, max-age=0');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow');

    if (defined('ATLANTIC_JSON') && ATLANTIC_JSON === true) {
        json_response([
            'error' => t('maintenance.api_unavailable'),
            'maintenance' => true,
        ], 503);
    }

    render_maintenance_page();
}
