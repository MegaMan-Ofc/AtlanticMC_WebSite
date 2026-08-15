<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/routes.php';
require_once __DIR__ . '/../includes/error_pages.php';

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
        render_not_found_page();
    }

    $_GET['slug'] = $categorySlug;
    $targetScript = $publicRoot . '/category.php';
}

$_SERVER['SCRIPT_FILENAME'] = $targetScript;
$_SERVER['SCRIPT_NAME'] = '/' . basename($targetScript);

require $targetScript;
exit;
