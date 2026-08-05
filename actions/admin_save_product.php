<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    save_product_from_admin($_POST);
    flash('success', t('messages.admin_product_saved'));
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_save_failed')));
}

redirect_admin('products');
