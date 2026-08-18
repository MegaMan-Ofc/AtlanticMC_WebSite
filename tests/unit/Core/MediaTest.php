<?php

declare(strict_types=1);

$throws(
    static fn () => validate_category_image_path('https://example.com/icon.png'),
    'Category images reject external paths.'
);
$assert(
    is_managed_upload_path('uploads/categories/category-' . str_repeat('a', 32) . '.png')
        && is_managed_upload_path('uploads/products/product-' . str_repeat('b', 32) . '.png')
        && !is_managed_upload_path('uploads/categories/file.php'),
    'Managed media paths only accept generated PNG locations.'
);

$testPngPath = $root . '/public_html/assets/logo1.png';
$testTextPath = $root . '/storage/test-upload.txt';
file_put_contents($testTextPath, 'not a png');

try {
    validate_png_file($testPngPath, (int) filesize($testPngPath));
    $assert(true, 'Valid PNG uploads pass content validation.');
} catch (Throwable) {
    $assert(false, 'Valid PNG uploads pass content validation.');
}

$throws(
    static fn () => validate_png_file($testTextPath, (int) filesize($testTextPath)),
    'Non-PNG upload content is rejected.'
);
@unlink($testTextPath);
$assert(
    format_admin_datetime('2026-08-06 14:20:00') === '06/08/2026 14:20'
        && format_admin_coupon_discount([
            'discount_type' => 'percentage',
            'discount_value' => 15,
        ]) === '15%',
    'Administrator formatting helpers are available to page and AJAX templates.'
);

