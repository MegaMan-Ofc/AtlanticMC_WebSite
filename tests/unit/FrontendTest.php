<?php

declare(strict_types=1);

$assert(
    is_file($root . '/public_html/ajax/admin-filter.php'),
    'The administrator AJAX filter endpoint exists.'
);

$adminJavaScript = file_get_contents(
    $root . '/public_html/js/admin.js'
);

$headerJavaScript = file_get_contents(
    $root . '/public_html/js/header.js'
);

$headerTemplate = file_get_contents(
    $root . '/includes/header.php'
);

$headerStyles = file_get_contents(
    $root . '/public_html/css/components.css'
);

$assert(
    is_string($headerJavaScript)
        && str_contains($headerJavaScript, 'data-smart-header')
        && str_contains($headerJavaScript, 'initialPrimaryOffset')
        && str_contains($headerJavaScript, 'is-hidden')
        && str_contains($headerJavaScript, 'is-fixed'),
    'The smart header script manages scroll direction, fixed state, and initial page offset.'
);

$assert(
    is_string($headerTemplate)
        && str_contains($headerTemplate, 'data-smart-header')
        && str_contains($headerTemplate, 'data-header-primary')
        && str_contains($headerTemplate, 'data-header-secondary'),
    'The public header exposes the hooks required by the smart header behavior.'
);

$assert(
    is_string($headerStyles)
        && str_contains($headerStyles, '.header-secondary.is-fixed')
        && str_contains($headerStyles, '.header-secondary.is-hidden'),
    'The shared header styles support fixed and hidden secondary header states.'
);


$assert(
    is_string($adminJavaScript)
        && str_contains(
            $adminJavaScript,
            'data-admin-filter-form'
        )
        && str_contains(
            $adminJavaScript,
            'data-admin-pagination'
        )
        && str_contains($adminJavaScript, 'data-admin-image-input')
        && str_contains($adminJavaScript, 'data-admin-slug-source')
        && str_contains($adminJavaScript, 'data-admin-discount-toggle')
        && is_file($root . '/templates/admin/coupons-results.php'),
    'Administrator JavaScript supports AJAX filters and pagination.'
);

$categoryDialogTemplate = file_get_contents($root . '/templates/admin/category-dialog.php');
$productDialogTemplate = file_get_contents($root . '/templates/admin/product-dialog.php');
$uploadProtection = file_get_contents($root . '/public_html/uploads/.htaccess');
$publicHtaccess = file_get_contents($root . '/public_html/.htaccess');
$assert(
    is_string($categoryDialogTemplate)
        && str_contains($categoryDialogTemplate, 'accept="image/png,.png"')
        && str_contains($categoryDialogTemplate, 'admin_delete_category.php')
        && is_string($productDialogTemplate)
        && str_contains($productDialogTemplate, 'accept="image/png,.png"')
        && str_contains($productDialogTemplate, 'name="discount_enabled"')
        && str_contains($productDialogTemplate, 'name="discount_price"')
        && is_string($uploadProtection)
        && str_contains($uploadProtection, 'Require all denied')
        && is_string($publicHtaccess)
        && str_contains($publicHtaccess, 'LimitRequestBody 6291456'),
    'Category and product forms use protected PNG uploads with a bounded request size.'
);

$catalogTemplate = file_get_contents($root . '/templates/catalog.php');
$productCardTemplate = file_get_contents($root . '/templates/product-card.php');
$cartTemplate = file_get_contents($root . '/templates/cart_panel.php');

$assert(
    is_string($catalogTemplate)
        && str_contains($catalogTemplate, "product-card.php")
        && is_string($productCardTemplate)
        && str_contains($productCardTemplate, 'product_has_discount')
        && str_contains($productCardTemplate, 'product_effective_price_cents')
        && is_string($cartTemplate)
        && str_contains($cartTemplate, 'cart-original-price'),
    'Catalogue and search pages share the same discounted product card rendering.'
);

$loginJavaScript = file_get_contents($root . '/public_html/js/login.js');
$headerTemplate = file_get_contents($root . '/includes/header.php');
$assert(
    is_string($loginJavaScript)
        && str_contains($loginJavaScript, 'value === "bedrock"')
        && is_string($headerTemplate)
        && str_contains($headerTemplate, "assets/ip.png")
        && str_contains($headerTemplate, "app.bedrock_server_ip"),
    'Bedrock login behavior and dual server addresses are present.'
);

$portugueseTranslations = require $root . '/translations/pt.php';
$englishTranslations = require $root . '/translations/en.php';
$assert(
    array_diff_key($portugueseTranslations, $englishTranslations) === []
        && array_diff_key($englishTranslations, $portugueseTranslations) === [],
    'Portuguese and English translation catalogues contain the same keys.'
);

$_SESSION['language'] = 'pt';
$assert(alternate_language() === 'en', 'Portuguese switches to English.');
$_SERVER['REQUEST_URI'] = '/admin?section=products';
$adminConfigured = true;
$adminAuthenticated = false;
ob_start();
require $root . '/templates/admin/page.php';
$adminLoginHtml = (string) ob_get_clean();
$assert(
    str_contains($adminLoginHtml, 'name="language" value="en"'),
    'The administrator login renders the language switch.'
);
$assert(
    str_contains($adminLoginHtml, 'name="return_to" value="admin?section=products"'),
    'The administrator language switch preserves the current section.'
);
$_SESSION = [];
$_SERVER['REQUEST_URI'] = '/';

