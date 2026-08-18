<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$category = query_string('slug');

if ($category === '') {
    $category = public_category_slug_from_request_uri((string) ($_SERVER['REQUEST_URI'] ?? '/')) ?? '';
}

require __DIR__ . '/catalog.php';
