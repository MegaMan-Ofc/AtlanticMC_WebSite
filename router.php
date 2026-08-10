<?php

declare(strict_types=1);

header_remove('X-Powered-By');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/routes.php';

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$requestPath = rawurldecode((string) (parse_url($requestUri, PHP_URL_PATH) ?? '/'));
$requestPath = '/' . ltrim($requestPath, '/');
$queryString = (string) (parse_url($requestUri, PHP_URL_QUERY) ?? '');
$relativePath = ltrim($requestPath, '/');

$scriptToName = [];
$pathToName = [];

foreach (public_routes() as $name => $route) {
    $scriptToName[$route['script']] = $name;
    $pathToName[$route['path']] = $name;
}


if ($relativePath === 'index' || $relativePath === 'index.php') {
    $location = route_url('home');
    header(
        'Location: ' . ($queryString === '' ? $location : $location . '?' . $queryString),
        true,
        301
    );
    exit;
}

if (isset($scriptToName[$relativePath])) {
    $location = route_url($scriptToName[$relativePath]);

    header(
        'Location: ' . ($queryString === '' ? $location : $location . '?' . $queryString),
        true,
        301
    );
    exit;
}

$trimmedPath = trim($relativePath, '/');

if ($relativePath !== $trimmedPath && isset($pathToName[$trimmedPath])) {
    $location = route_url($pathToName[$trimmedPath]);

    header(
        'Location: ' . ($queryString === '' ? $location : $location . '?' . $queryString),
        true,
        301
    );
    exit;
}


$publicRoot = __DIR__ . '/public_html';
$filePath = $publicRoot . $requestPath;

if ($requestPath !== '/' && is_file($filePath)) {
    return false;
}

$routeName = $requestPath === '/'
    ? 'home'
    : ($pathToName[trim($requestPath, '/')] ?? null);

if (!is_string($routeName)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo '404 Not Found';
    exit;
}

$route = public_routes()[$routeName];

$_SERVER['SCRIPT_FILENAME'] = $publicRoot . '/' . $route['script'];
$_SERVER['SCRIPT_NAME'] = '/' . $route['script'];

require $_SERVER['SCRIPT_FILENAME'];
