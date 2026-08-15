<?php

declare(strict_types=1);

$bannerMigrationMysql = file_get_contents($root . '/database/migrations/mysql/011_home_banner_customization.php');
$bannerMigrationSqlite = file_get_contents($root . '/database/migrations/sqlite/011_home_banner_customization.php');
$bannerTemplate = file_get_contents($root . '/templates/home/category-banner.php');
$adminHomeTemplate = file_get_contents($root . '/templates/admin/recommended.php');
$adminHomeJavaScript = file_get_contents($root . '/public_html/js/admin.js');
$adminHomeCss = file_get_contents($root . '/public_html/css/pages/admin.css');
$homeCss = file_get_contents($root . '/public_html/css/pages/home.css');

$assert(
    is_string($bannerMigrationMysql)
        && str_contains($bannerMigrationMysql, 'home_banner_kicker')
        && str_contains($bannerMigrationMysql, 'home_banner_style')
        && is_string($bannerMigrationSqlite)
        && str_contains($bannerMigrationSqlite, 'home_banner_show_cta'),
    'Homepage banner customization has matching MySQL and SQLite migrations.'
);

$defaults = home_banner_settings([
    'home_banner_style' => 'invalid',
    'home_banner_image_side' => 'invalid',
    'home_banner_image_size' => 'invalid',
]);

$assert(
    $defaults['style'] === 'auto'
        && $defaults['image_side'] === 'right'
        && $defaults['image_size'] === 'normal'
        && $defaults['show_watermark'] === true
        && $defaults['show_cta'] === true,
    'Banner settings normalize invalid or missing appearance values to safe defaults.'
);

$assert(
    home_banner_is_customized([
        'home_banner_style' => 'sunset',
        'home_banner_image_side' => 'right',
        'home_banner_image_size' => 'normal',
        'home_banner_show_watermark' => 1,
        'home_banner_show_cta' => 1,
    ]),
    'Non-default banner appearance is detected as customization.'
);

$assert(
    is_string($adminHomeTemplate)
        && str_contains($adminHomeTemplate, 'data-admin-home-banner-edit')
        && str_contains($adminHomeTemplate, 'id="admin-home-banner-dialog"')
        && str_contains($adminHomeTemplate, 'data-admin-home-banner-preview')
        && is_file($root . '/public_html/actions/admin_save_home_banner.php'),
    'Banner categories expose an edit action with a dedicated customization dialog and endpoint.'
);

$assert(
    is_string($adminHomeJavaScript)
        && str_contains($adminHomeJavaScript, 'updateAdminHomeBannerPreview')
        && str_contains($adminHomeJavaScript, 'applyAdminHomeBannerSettingsToCard')
        && str_contains($adminHomeJavaScript, 'admin_save_home_banner.php') === false
        && is_string($adminHomeCss)
        && str_contains($adminHomeCss, '.admin-home-banner-preview'),
    'Banner customization uses reusable AJAX behavior and a responsive live preview.'
);

$assert(
    is_string($bannerTemplate)
        && str_contains($bannerTemplate, 'data-banner-style')
        && str_contains($bannerTemplate, "['image_side']")
        && str_contains($bannerTemplate, "['show_watermark']")
        && is_string($homeCss)
        && str_contains($homeCss, 'data-banner-style="atlantic"')
        && str_contains($homeCss, 'home-category-banner--image-left'),
    'The storefront banner renders the saved style, image placement, watermark and responsive variants.'
);
