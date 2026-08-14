<?php

declare(strict_types=1);

$assert(
    RECOMMENDED_PRODUCT_SLOTS === 5
        && is_file($root . '/database/migrations/mysql/007_recommended_products.php')
        && is_file($root . '/database/migrations/sqlite/007_recommended_products.php')
        && is_file($root . '/templates/admin/recommended.php')
        && is_file($root . '/public_html/actions/admin_save_recommended.php')
        && is_file($root . '/public_html/actions/admin_remove_recommended.php')
        && is_file($root . '/public_html/actions/admin_reorder_recommended.php'),
    'Recommended products use five managed slots with database migrations and administrator actions.'
);

$homeTemplate = file_get_contents($root . '/public_html/index.php');
$adminRecommendedTemplate = file_get_contents($root . '/templates/admin/recommended.php');
$adminJavaScript = file_get_contents($root . '/public_html/js/admin.js');

$assert(
    is_string($homeTemplate)
        && str_contains($homeTemplate, 'homeRecommendedProducts')
        && str_contains($homeTemplate, 'home-recommended-grid')
        && is_string($adminRecommendedTemplate)
        && str_contains($adminRecommendedTemplate, 'data-admin-recommended-grid')
        && str_contains($adminRecommendedTemplate, 'data-admin-recommended-edit')
        && is_string($adminJavaScript)
        && str_contains($adminJavaScript, 'saveAdminRecommendedOrder')
        && str_contains($adminJavaScript, 'dragstart')
        && str_contains($adminJavaScript, 'data-admin-recommended-edit'),
    'Recommended products render on the homepage and support preview, replacement, removal, and drag ordering in the administrator.'
);
