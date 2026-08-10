<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/routes.php';

$routeName = public_route_name_from_request_uri((string) ($_SERVER['REQUEST_URI'] ?? '/'));

if ($routeName === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

if ($routeName === 'home') {
    return;
}

$route = public_routes()[$routeName];
$publicRoot = dirname(__DIR__) . '/public_html';
$targetScript = $publicRoot . '/' . $route['script'];




$_SERVER['SCRIPT_FILENAME'] = $targetScript;
$_SERVER['SCRIPT_NAME'] = '/' . $route['script'];

require $targetScript;
exit;
