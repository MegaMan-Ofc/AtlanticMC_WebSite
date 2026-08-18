<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$legalPageKey = trim((string) ($legalPageKey ?? ''));

if (!in_array($legalPageKey, ['privacy', 'terms', 'purchase-policy', 'rules'], true)) {
    render_not_found_page();
}

$legalPage = legal_page_data($legalPageKey);
$pageTitle = $legalPage['pageTitle'];
$pageDescription = $legalPage['description'];
$bodyClass = 'page-' . $legalPageKey;
$pageStyles = ['css/pages/legal.css'];
