<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    save_recommended_product(request_int('slot'), request_int('product_id'));
    flash('success', t('messages.admin_recommended_saved'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_save_failed')));
}

redirect_admin('recommended');
