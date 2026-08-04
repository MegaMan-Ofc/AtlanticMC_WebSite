<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$category = 'rubis';
$page = category_configuration($category);
$pageTitle = $page['title'];
$pageHeading = $page['heading'];
$pageDescription = $page['description'];
$bodyClass = $page['bodyClass'];
$pageStyles = $page['styles'];
$products = products_by_category($category);
