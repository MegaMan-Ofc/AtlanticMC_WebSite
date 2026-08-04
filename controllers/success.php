<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Atlantic Anarchy - Order';
$pageDescription = 'Atlantic Anarchy order status.';
$bodyClass = 'page-success success-main';
$pageStyles = ['css/pages/success.css'];

$orderToken = is_string($_GET['order'] ?? null)
    ? trim($_GET['order'])
    : (string) ($_SESSION['last_order_token'] ?? '');
$order = null;

if (preg_match('/^[a-f0-9]{48}$/', $orderToken)) {
    $order = order_by_token($orderToken);
}
