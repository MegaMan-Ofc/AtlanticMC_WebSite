<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$searchQuery = query_string('q');

if (strlen($searchQuery) > 80) {
    $searchQuery = function_exists('mb_substr')
        ? mb_substr($searchQuery, 0, 80)
        : substr($searchQuery, 0, 80);
}

$searchCategory = strtolower(query_string('category'));
$searchSort = strtolower(query_string('sort', 'relevance'));
$searchDiscountOnly = query_string('discount') === '1';
$searchCategories = all_store_categories(false);

if ($searchCategory !== '' && store_category_by_slug($searchCategory, false) === null) {
    $searchCategory = '';
}

if (!in_array($searchSort, product_search_sort_options(), true)) {
    $searchSort = 'relevance';
}

$products = product_search_results($searchQuery, $searchCategory, $searchDiscountOnly, $searchSort);
track_product_impressions($products);
$pageTitle = t('search.page_title');
$pageDescription = t('search.description');
$bodyClass = 'page-search';
$pageStyles = ['css/pages/catalog.css', 'css/pages/search.css'];
