<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    delete_category_from_admin((int) ($_POST['id'] ?? 0));
    flash('success', t('messages.admin_category_deleted'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_delete_failed')));
}

redirect_admin('categories');
