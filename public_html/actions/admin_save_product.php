<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

$uploadedImage = null;

try {
    $uploadedImage = save_uploaded_png($_FILES['image_file'] ?? [], 'products');
    save_product_from_admin($_POST, $uploadedImage);
    flash('success', t('messages.admin_product_saved'));
} catch (InvalidArgumentException $error) {
    if (is_string($uploadedImage)) {
        delete_managed_upload_file($uploadedImage);
    }

    flash('error', $error->getMessage());
} catch (Throwable $error) {
    if (is_string($uploadedImage)) {
        delete_managed_upload_file($uploadedImage);
    }

    flash('error', public_error_message($error, t('messages.admin_save_failed')));
}

redirect_admin('products');
