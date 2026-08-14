<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$category = query_string('slug');

if ($category === '') {
    $category = public_category_slug_from_request_uri((string) ($_SERVER['REQUEST_URI'] ?? '/')) ?? '';
}

require __DIR__ . '/catalog.php';
