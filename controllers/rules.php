<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$legalPage = legal_page_data('rules');
$pageTitle = $legalPage['pageTitle'];
$pageDescription = $legalPage['description'];
$bodyClass = 'page-rules';
$pageStyles = ['css/pages/legal.css'];
