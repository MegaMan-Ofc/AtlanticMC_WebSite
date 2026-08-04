<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

$statement = db()->prepare('DELETE FROM coupons WHERE id = :id');
$statement->execute(['id' => request_int('id')]);
flash('success', 'Coupon deleted.');
redirect('admin.php');
