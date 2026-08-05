<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$legalPage = legal_page_data('purchase-policy');
$pageTitle = $legalPage['pageTitle'];
$pageDescription = $legalPage['description'];
$bodyClass = 'page-purchase-policy';
$pageStyles = ['css/pages/legal.css'];
