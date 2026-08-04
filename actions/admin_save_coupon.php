<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    save_coupon_from_admin($_POST);
    flash('success', 'Coupon saved.');
} catch (Throwable $error) {
    flash('error', $error->getMessage());
}

redirect('admin.php');
