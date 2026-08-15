<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$category = strtolower(trim((string) ($category ?? '')));
$storeCategory = store_category_by_slug($category, false);

if ($storeCategory === null) {
    render_not_found_page();
}

$page = category_configuration($category);
$pageTitle = $page['title'];
$pageHeading = $page['heading'];
$pageDescription = $page['description'];
$bodyClass = $page['bodyClass'];
$pageStyles = $page['styles'];
$products = products_by_category($category);
track_product_impressions($products);
