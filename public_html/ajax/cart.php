<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('ajax_cart', 120, 60);

$operation = request_string('operation');
$message = '';

try {
    switch ($operation) {
        case 'add':
            cart_add(request_int('product_id'), max(1, request_int('quantity', 1)));
            $message = t('messages.cart_added');
            break;

        case 'update':
            $quantities = $_POST['quantities'] ?? [];
            cart_update(is_array($quantities) ? $quantities : []);
            $message = t('messages.cart_updated');
            break;

        case 'remove':
            cart_remove(request_int('product_id'));
            $message = t('messages.cart_removed');
            break;

        case 'apply_coupon':
            if (tebex_is_configured() && !tebex_coupons_enabled()) {
                throw new InvalidArgumentException(t('tebex.coupons_disabled'));
            }

            $summary = cart_summary();
            $coupon = validate_coupon(request_string('coupon_code'), (int) $summary['subtotal_cents']);
            $_SESSION['coupon_code'] = (string) $coupon['code'];
            $message = t('messages.coupon_applied');
            break;

        case 'remove_coupon':
            unset($_SESSION['coupon_code']);
            $message = t('messages.coupon_removed');
            break;

        default:
            throw new InvalidArgumentException(t('validation.invalid_cart_operation'));
    }

    $cart = cart_summary();
    $cartHtml = null;

    if (request_string('render_cart') === '1') {
        ob_start();
        require BASE_PATH . '/templates/cart_panel.php';
        $rendered = ob_get_clean();
        $cartHtml = is_string($rendered) ? $rendered : null;
    }

    json_response([
        'message' => $message,
        'data' => [
            'cart_count' => (int) $cart['item_count'],
            'cart_html' => $cartHtml,
            'subtotal' => format_money((int) $cart['subtotal_cents']),
            'discount' => format_money((int) $cart['discount_cents']),
            'total' => format_money((int) $cart['total_cents']),
        ],
    ]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
}
