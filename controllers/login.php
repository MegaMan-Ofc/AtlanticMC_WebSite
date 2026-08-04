<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Atlantic Anarchy - Minecraft Recipient';
$pageDescription = 'Choose the Minecraft account that will receive the purchase.';
$bodyClass = 'page-login';
$pageStyles = ['css/pages/auth.css'];
$loginRecipient = current_minecraft_recipient();
$returnTo = safe_return_path(
    is_string($_GET['return_to'] ?? null) ? $_GET['return_to'] : ($_SESSION['recipient_return_to'] ?? 'index.php'),
    'index.php'
);
