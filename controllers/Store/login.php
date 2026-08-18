<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$pageTitle = t('login.page_title');
$pageDescription = t('login.page_description');
$bodyClass = 'page-login';
$pageStyles = ['css/pages/auth.css'];
$pageScripts = ['js/login.js'];
$loginRecipient = current_minecraft_recipient();
$returnTo = safe_return_path(
    is_string($_GET['return_to'] ?? null) ? $_GET['return_to'] : ($_SESSION['recipient_return_to'] ?? route_path('home')),
    route_path('home')
);
