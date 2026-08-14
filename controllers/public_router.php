<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/routes.php';

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$routeName = public_route_name_from_request_uri($requestUri);
$publicRoot = dirname(__DIR__) . '/public_html';

if ($routeName === 'home') {
    return;
}

if ($routeName !== null) {
    $route = public_routes()[$routeName];
    $targetScript = $publicRoot . '/' . $route['script'];
} else {
    $categorySlug = public_category_slug_from_request_uri($requestUri);

    if ($categorySlug === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
        exit;
    }

    $_GET['slug'] = $categorySlug;
    $targetScript = $publicRoot . '/category.php';
}

$_SERVER['SCRIPT_FILENAME'] = $targetScript;
$_SERVER['SCRIPT_NAME'] = '/' . basename($targetScript);

require $targetScript;
exit;
