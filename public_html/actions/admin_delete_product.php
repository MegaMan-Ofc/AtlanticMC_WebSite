<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

require_post();
verify_csrf();
require_admin();

try {
    delete_product_from_admin(request_int('id'));
    flash('success', t('messages.admin_product_deleted'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
} catch (Throwable $error) {
    flash(
        'error',
        public_error_message(
            $error,
            t('messages.admin_delete_failed')
        )
    );
}

redirect_admin('products');
