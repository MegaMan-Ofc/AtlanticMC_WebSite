<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/coupons.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/minecraft_auth.php';
require_once __DIR__ . '/tebex.php';
require_once __DIR__ . '/orders.php';

if (!defined('ATLANTIC_STATELESS') || ATLANTIC_STATELESS !== true) {
    start_store_session();
}

send_security_headers();

if ((bool) config('app.debug', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);

    $debug = (bool) config('app.debug', false);
    $plainMessage = $debug
        ? $error->getMessage()
        : 'Ocorreu um erro interno. Tenta novamente mais tarde.';

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

    echo '<!doctype html><html lang="pt"><meta charset="utf-8"><title>Erro</title>'
        . '<body style="font-family:sans-serif;padding:2rem;background:#0b0e13;color:#fff">'
        . '<h1>Atlantic Anarchy</h1><p>' . e($plainMessage) . '</p>'
        . '<p><a style="color:#8fd3ff" href="' . e(url('index.php')) . '">Voltar à loja</a></p></body></html>';
});

initialize_database();
