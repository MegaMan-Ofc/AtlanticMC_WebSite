<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = t('home.page_title');
$pageDescription = t('home.description');
$bodyClass = 'page-home';
$pageStyles = ['css/pages/home.css'];
$homeCategoryLayout = home_store_category_layout();
$homeTopCategory = $homeCategoryLayout['top'];
$homeCategories = $homeCategoryLayout['grid'];
$homeBottomCategory = $homeCategoryLayout['bottom'];
$homeHasCategories = $homeTopCategory !== null || $homeCategories !== [] || $homeBottomCategory !== null;
$homeRecommendedProducts = recommended_products_for_home();
track_product_impressions($homeRecommendedProducts);
