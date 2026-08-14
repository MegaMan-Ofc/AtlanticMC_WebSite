<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    remove_recommended_product(request_int('slot'));
    flash('success', t('messages.admin_recommended_removed'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_delete_failed')));
}

redirect_admin('recommended');
