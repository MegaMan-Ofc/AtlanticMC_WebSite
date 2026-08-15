<?php

declare(strict_types=1);

$modules = require __DIR__ . '/modules.php';

foreach ($modules as $module) {
    require_once __DIR__ . '/' . $module;
}

unset($modules, $module);

try {
    $debugEnabled = (bool) config('app.debug', false) && !is_production();
    ini_set('display_errors', $debugEnabled ? '1' : '0');
    error_reporting(E_ALL);
    validate_runtime_configuration();
    enforce_https_request();
} catch (Throwable $error) {
    ini_set('display_errors', '0');
    error_log('Invalid AtlanticStore configuration: ' . $error->getMessage());

    if (PHP_SAPI === 'cli') {
        throw $error;
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Application configuration error.';
    exit;
}

if (!defined('ATLANTIC_STATELESS') || ATLANTIC_STATELESS !== true) {
    start_store_session();
}

header('X-Request-ID: ' . request_id());
send_security_headers();

set_exception_handler(static function (Throwable $error): void {
    log_exception($error);

    $plainMessage = (bool) config('app.debug', false) && !is_production()
        ? $error->getMessage()
        : t('messages.internal_error');

    if (defined('ATLANTIC_JSON') && ATLANTIC_JSON === true) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(['error' => $plainMessage], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<!doctype html><html lang="' . e(current_language()) . '"><meta charset="utf-8"><title>' . e(t('common.error', [], 'Error')) . '</title>'
        . '<body style="font-family:sans-serif;padding:2rem;background:#0b0e13;color:#fff">'
        . '<h1>Atlantic SMP</h1><p>' . e($plainMessage) . '</p>'
        . '<p><a style="color:#8fd3ff" href="' . e(route_url('home')) . '">' . e(t('messages.back_to_store')) . '</a></p></body></html>';
});

enforce_maintenance_mode();

if ((!defined('ATLANTIC_STATELESS') || ATLANTIC_STATELESS !== true) && !maintenance_is_enabled()) {
    track_public_page_view();
}
