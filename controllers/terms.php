<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$legalPage = legal_page_data('terms');
$pageTitle = $legalPage['pageTitle'];
$pageDescription = $legalPage['description'];
$bodyClass = 'page-terms';
$pageStyles = ['css/pages/legal.css'];
