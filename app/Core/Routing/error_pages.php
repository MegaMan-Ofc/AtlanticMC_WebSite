<?php

declare(strict_types=1);

function render_not_found_page(): never
{
    http_response_code(404);

    $basePath = request_base_path();
    $script = BASE_PATH . '/public_html/404.php';

    $_SERVER['SCRIPT_FILENAME'] = $script;
    $_SERVER['SCRIPT_NAME'] = ($basePath === '' ? '' : $basePath) . '/404.php';

    require $script;
    exit;
}
