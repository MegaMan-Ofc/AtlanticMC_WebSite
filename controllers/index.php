<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = t('home.page_title');
$pageDescription = t('home.description');
$bodyClass = 'page-home';
$pageStyles = ['css/pages/home.css'];
$homeCategories = home_store_categories();
$homeHeroCategory = store_category_by_slug('keys', false);
