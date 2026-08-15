<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

http_response_code(404);

$pageTitle = t('not_found.page_title');
$pageDescription = t('not_found.description');
$pageRobots = 'noindex, follow';
$bodyClass = 'page-not-found';
$pageStyles = ['css/pages/not-found.css'];
