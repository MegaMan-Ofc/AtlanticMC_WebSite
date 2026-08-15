<?php

declare(strict_types=1);

$notFoundPage = file_get_contents($root . '/public_html/404.php');
$notFoundController = file_get_contents($root . '/controllers/not_found.php');
$notFoundTemplate = file_get_contents($root . '/templates/errors/not-found.php');
$notFoundStyles = file_get_contents($root . '/public_html/css/pages/not-found.css');
$errorPages = file_get_contents($root . '/includes/error_pages.php');
$headTemplate = file_get_contents($root . '/includes/head.php');
$router = file_get_contents($root . '/router.php');
$publicRouter = file_get_contents($root . '/controllers/public_router.php');
$catalogController = file_get_contents($root . '/controllers/catalog.php');
$publicHtaccess = file_get_contents($root . '/public_html/.htaccess');
$portugueseTranslations = require $root . '/translations/pt.php';
$englishTranslations = require $root . '/translations/en.php';

$assert(
    is_string($notFoundPage)
        && is_string($notFoundController)
        && is_string($notFoundTemplate)
        && is_string($notFoundStyles)
        && str_contains($notFoundPage, "controllers/not_found.php")
        && str_contains($notFoundPage, "templates/errors/not-found.php")
        && str_contains($notFoundController, 'http_response_code(404)')
        && str_contains($notFoundController, "'css/pages/not-found.css'")
        && str_contains($notFoundController, "'noindex, follow'"),
    'The custom 404 page uses a dedicated controller, template, stylesheet and non-indexable response metadata.'
);

$assert(
    is_string($notFoundTemplate)
        && str_contains($notFoundTemplate, "route_url('home')")
        && str_contains($notFoundTemplate, "route_url('search')")
        && str_contains($notFoundTemplate, "config('app.discord_url')")
        && str_contains($notFoundTemplate, "not_found.title")
        && str_contains($notFoundTemplate, "not_found.description"),
    'The 404 view provides localized recovery actions for the store, search and support.'
);

$assert(
    is_string($errorPages)
        && substr_count($router ?: '', 'render_not_found_page()') >= 1
        && substr_count($publicRouter ?: '', 'render_not_found_page()') >= 1
        && substr_count($catalogController ?: '', 'render_not_found_page()') >= 1
        && is_string($publicHtaccess)
        && str_contains($publicHtaccess, 'ErrorDocument 404 /404.php'),
    'Development, application and Apache routing converge on the same custom 404 response.'
);

$assert(
    public_route_name_from_request_uri('/missing/route') === null
        && public_category_slug_from_request_uri('/missing/route') === null
        && public_route_name_from_request_uri('/missing-page') === null
        && public_category_slug_from_request_uri('/missing-page') === 'missing-page',
    'Unknown nested routes resolve as 404s while valid single-segment slugs remain eligible for dynamic category lookup.'
);

$assert(
    is_string($headTemplate)
        && str_contains($headTemplate, 'name="robots"')
        && isset($portugueseTranslations['not_found.page_title'])
        && isset($portugueseTranslations['not_found.title'])
        && isset($englishTranslations['not_found.page_title'])
        && isset($englishTranslations['not_found.title']),
    'The shared head supports robots metadata and the 404 copy is available in both languages.'
);
