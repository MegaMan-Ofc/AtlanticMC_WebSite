<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_get();

if (!admin_is_authenticated()) {
    json_response(
        [
            'error' => t('validation.admin_required'),
            'data' => [
                'redirect_url' => route_url('admin'),
            ],
        ],
        403
    );
}

enforce_rate_limit('admin_filter', 240, 60);

$renderTemplate = static function (
    string $template,
    array $variables
): string {
    extract($variables, EXTR_SKIP);

    ob_start();

    require BASE_PATH . '/templates/admin/' . $template . '.php';

    $html = ob_get_clean();

    return is_string($html) ? $html : '';
};

$section = query_string('section');

if ($section === 'products') {
    $adminCategoryOptions = all_store_categories(true);
    $adminProductFilters = admin_product_filters();
    $adminProducts = all_products_admin($adminProductFilters);

    json_response([
        'data' => [
            'html' => $renderTemplate(
                'products-results',
                [
                    'adminProducts' => $adminProducts,
                    'adminCategoryOptions' => $adminCategoryOptions,
                ]
            ),
            'url' => admin_section_url(
                'products',
                admin_product_query_parameters($adminProductFilters)
            ),
            'count_label' => t('admin.results_count', [
                'count' => count($adminProducts),
            ]),
        ],
    ]);
}

if ($section === 'coupons') {
    $adminCouponFilters = admin_coupon_filters();
    $adminCoupons = all_coupons_admin($adminCouponFilters);

    json_response([
        'data' => [
            'html' => $renderTemplate(
                'coupons-results',
                [
                    'adminCoupons' => $adminCoupons,
                ]
            ),
            'url' => admin_section_url(
                'coupons',
                admin_coupon_query_parameters($adminCouponFilters)
            ),
            'count_label' => t('admin.results_count', [
                'count' => count($adminCoupons),
            ]),
        ],
    ]);
}

if ($section === 'orders') {
    $adminOrderFilters = admin_order_filters();
    $adminOrdersPage = admin_orders_page($adminOrderFilters);
    $query = admin_order_query_parameters($adminOrderFilters);

    if ((int) $adminOrdersPage['page'] > 1) {
        $query['page'] = (int) $adminOrdersPage['page'];
    }

    json_response([
        'data' => [
            'html' => $renderTemplate(
                'orders-results',
                [
                    'adminOrderFilters' => $adminOrderFilters,
                    'adminOrdersPage' => $adminOrdersPage,
                ]
            ),
            'url' => admin_section_url('orders', $query),
            'count_label' => t('admin.results_count', [
                'count' => (int) $adminOrdersPage['total'],
            ]),
        ],
    ]);
}

json_response(
    [
        'error' => t('validation.invalid_admin_section'),
    ],
    422
);
