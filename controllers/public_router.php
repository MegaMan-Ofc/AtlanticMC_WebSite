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
$publicRoot = dirname(__DIR__) . '/public';
$targetScript = $publicRoot . '/' . $route['script'];

// Some shared hosts send extensionless requests to index.php before their
// rewrite layer runs. Keep route/canonical helpers aware of the actual page
// while delegating rendering to the existing public page entry point.
$_SERVER['SCRIPT_FILENAME'] = $targetScript;
$_SERVER['SCRIPT_NAME'] = '/' . $route['script'];

require $targetScript;
exit;
