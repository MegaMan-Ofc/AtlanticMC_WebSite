<?php

declare(strict_types=1);

header_remove('X-Powered-By');

require_once __DIR__ . '/app/Core/Config/config.php';
require_once __DIR__ . '/app/Core/Routing/routes.php';
require_once __DIR__ . '/app/Core/Routing/error_pages.php';

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

if ($relativePath !== $trimmedPath) {
    if (isset($pathToName[$trimmedPath])) {
        $location = route_url($pathToName[$trimmedPath]);
        header(
            'Location: ' . ($queryString === '' ? $location : $location . '?' . $queryString),
            true,
            301
        );
        exit;
    }

    $categorySlug = public_category_slug_from_path($trimmedPath);

    if ($categorySlug !== null) {
        $location = category_url($categorySlug);
        header(
            'Location: ' . ($queryString === '' ? $location : $location . '?' . $queryString),
            true,
            301
        );
        exit;
    }
}

$publicRoot = __DIR__ . '/public_html';
$filePath = $publicRoot . $requestPath;

if ($requestPath !== '/' && is_file($filePath)) {
    return false;
}

if ($requestPath === '/') {
    $targetScript = $publicRoot . '/index.php';
} elseif (isset($pathToName[$trimmedPath])) {
    $route = public_routes()[$pathToName[$trimmedPath]];
    $targetScript = $publicRoot . '/' . $route['script'];
} else {
    $categorySlug = public_category_slug_from_path($trimmedPath);

    if ($categorySlug === null) {
        render_not_found_page();
    }

    $_GET['slug'] = $categorySlug;
    $targetScript = $publicRoot . '/category.php';
}

$_SERVER['SCRIPT_FILENAME'] = $targetScript;
$_SERVER['SCRIPT_NAME'] = '/' . basename($targetScript);

require $targetScript;
