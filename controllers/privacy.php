<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$legalPage = legal_page_data('privacy');
$pageTitle = $legalPage['pageTitle'];
$pageDescription = $legalPage['description'];
$bodyClass = 'page-privacy';
$pageStyles = ['css/pages/legal.css'];
