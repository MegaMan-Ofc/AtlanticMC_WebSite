<?php

declare(strict_types=1);

$homeTemplate = file_get_contents($root . '/public_html/index.php');
$adminTemplate = file_get_contents($root . '/templates/admin/recommended.php');
$adminJavaScript = file_get_contents($root . '/public_html/js/admin.js');
$adminController = file_get_contents($root . '/controllers/admin.php');

$assert(
    is_file($root . '/database/migrations/mysql/008_home_category_layout.php')
        && is_file($root . '/database/migrations/sqlite/008_home_category_layout.php')
        && is_file($root . '/includes/admin_home.php')
        && is_file($root . '/public_html/actions/admin_save_home_categories.php')
        && is_file($root . '/templates/admin/home-category-card.php'),
    'Homepage category layout has migrations, administrator helpers, endpoint, and reusable category preview markup.'
);

$assert(
    is_string($homeTemplate)
        && str_contains($homeTemplate, 'homeTopCategory')
        && str_contains($homeTemplate, 'homeBottomCategory')
        && str_contains($homeTemplate, 'templates/home/category-banner.php')
        && !str_contains($homeTemplate, 'homeHeroCategory'),
    'The homepage renders optional top and bottom category banners from the managed layout.'
);

$assert(
    is_string($adminTemplate)
        && str_contains($adminTemplate, 'data-admin-home-layout')
        && str_contains($adminTemplate, 'data-admin-home-zone="top"')
        && str_contains($adminTemplate, 'data-admin-home-zone="grid"')
        && str_contains($adminTemplate, 'data-admin-home-zone="bottom"')
        && is_string($adminJavaScript)
        && str_contains($adminJavaScript, 'saveAdminHomeLayout')
        && str_contains($adminJavaScript, 'adminHomeDraggedCategory')
        && is_string($adminController)
        && str_contains($adminController, 'admin_home_category_layout'),
    'The Homepage administrator section keeps recommended products first and adds drag-and-drop category layout controls.'
);
