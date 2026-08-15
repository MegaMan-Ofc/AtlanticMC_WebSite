<?php

declare(strict_types=1);

function cart_raw(): array
{
    $cart = $_SESSION['cart'] ?? [];

    return is_array($cart) ? $cart : [];
}

function cart_add(int $productId, int $quantity = 1): void
{
    if (product_by_id($productId) === null) {
        throw new InvalidArgumentException(t('validation.invalid_product'));
    }

    $max = (int) config('app.max_cart_quantity', 20);
    $cart = cart_raw();
    $current = (int) ($cart[$productId] ?? 0);
    $next = min($max, max(1, $current + $quantity));
    $cart[$productId] = $next;
    $_SESSION['cart'] = $cart;

    $added = max(0, $next - $current);

    if ($added > 0 && function_exists('track_product_cart_add')) {
        track_product_cart_add($productId, $added);
    }
}

function cart_update(array $quantities): void
{
    $max = (int) config('app.max_cart_quantity', 20);
    $cart = cart_raw();

    foreach ($quantities as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if (!array_key_exists($productId, $cart)) {
            continue;
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
            continue;
        }

        $previous = (int) $cart[$productId];
        $next = min($max, $quantity);
        $cart[$productId] = $next;

        if ($next > $previous && function_exists('track_product_cart_add')) {
            track_product_cart_add($productId, $next - $previous);
        }
    }

    $_SESSION['cart'] = $cart;
}

function cart_remove(int $productId): void
{
    $cart = cart_raw();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart'], $_SESSION['coupon_code']);
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_raw()));
}

function cart_summary(): array
{
    $cart = cart_raw();
    $products = products_by_ids(array_keys($cart));
    $items = [];
    $subtotal = 0;
    $normalizedCart = [];

    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = max(1, min((int) config('app.max_cart_quantity', 20), (int) $quantity));
        $product = $products[$productId] ?? null;

        if ($product === null) {
            continue;
        }

        $lineTotal = product_effective_price_cents($product) * $quantity;
        $subtotal += $lineTotal;
        $normalizedCart[$productId] = $quantity;
        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'line_total_cents' => $lineTotal,
        ];
    }

    $_SESSION['cart'] = $normalizedCart;

    $coupon = null;
    $discount = 0;
    $couponCode = is_string($_SESSION['coupon_code'] ?? null) ? $_SESSION['coupon_code'] : '';

    if ($couponCode !== '' && tebex_is_configured() && !tebex_coupons_enabled()) {
        unset($_SESSION['coupon_code']);
        $couponCode = '';
    }

    if ($couponCode !== '' && $subtotal > 0) {
        try {
            $coupon = validate_coupon($couponCode, $subtotal);
            $discount = coupon_discount($coupon, $subtotal);
        } catch (InvalidArgumentException) {
            unset($_SESSION['coupon_code']);
        }
    }

    $total = max(0, $subtotal - $discount);
    $vatRate = (float) config('app.vat_rate', 0.23);
    $vatIncluded = $vatRate > 0 ? (int) round($total * ($vatRate / (1 + $vatRate))) : 0;

    return [
        'items' => $items,
        'item_count' => array_sum($normalizedCart),
        'subtotal_cents' => $subtotal,
        'discount_cents' => $discount,
        'total_cents' => $total,
        'vat_included_cents' => $vatIncluded,
        'coupon' => $coupon,
        'currency' => (string) config('app.currency', 'EUR'),
    ];
}
