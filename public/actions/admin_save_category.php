<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    save_category_from_admin($_POST);
    flash('success', t('messages.admin_category_saved'));
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_save_failed')));
}

redirect_admin('categories');
