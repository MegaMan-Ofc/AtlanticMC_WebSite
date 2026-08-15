<?php

declare(strict_types=1);

function tebex_is_configured(): bool
{
    return (bool) config('app.payments_enabled', false)
        && trim((string) config('tebex.public_token', '')) !== '';
}

function tebex_webhook_is_configured(): bool
{
    return trim((string) config('tebex.webhook_secret', '')) !== '';
}

function tebex_coupons_enabled(): bool
{
    return (bool) config('tebex.coupons_enabled', false);
}

function tebex_money_to_cents(mixed $value): ?int
{
    if (!is_int($value) && !is_float($value) && !is_string($value)) {
        return null;
    }

    $normalized = trim((string) $value);

    if ($normalized === '' || !preg_match('/^\d+(?:\.\d+)?$/', $normalized)) {
        return null;
    }

    $amount = (float) $normalized;

    if (!is_finite($amount) || $amount < 0) {
        return null;
    }

    return (int) round($amount * 100);
}

function tebex_basket_totals(array $basket): array
{
    $totalCents = tebex_money_to_cents($basket['total_price'] ?? null);
    $currency = strtoupper(trim((string) ($basket['currency'] ?? '')));

    if ($totalCents === null || !preg_match('/^[A-Z]{3}$/', $currency)) {
        throw new RuntimeException(t('tebex.basket_totals_missing'));
    }

    return [
        'total_cents' => $totalCents,
        'currency' => $currency,
    ];
}

function tebex_create_checkout(array $order, array $items): array
{
    if (!tebex_is_configured()) {
        throw new RuntimeException(t('tebex.not_configured'));
    }

    foreach ($items as $item) {
        if (trim((string) ($item['product']['tebex_package_id'] ?? '')) === '') {
            throw new RuntimeException(t('tebex.missing_package'));
        }
    }

    $couponCode = trim((string) ($order['coupon_code'] ?? ''));

    if ($couponCode !== '' && !tebex_coupons_enabled()) {
        throw new RuntimeException(t('tebex.coupons_disabled'));
    }

    $completeUrl = route_url('success', ['order' => (string) $order['public_token']]);
    $cancelUrl = route_url('cart');

    if (!filter_var($completeUrl, FILTER_VALIDATE_URL) || !filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException(t('tebex.app_url'));
    }

    $token = rawurlencode((string) config('tebex.public_token'));
    $serverUsername = minecraft_server_username(
        (string) $order['minecraft_name'],
        (string) $order['minecraft_platform']
    );
    $createResponse = http_request_json(
        'POST',
        'https://headless.tebex.io/api/accounts/' . $token . '/baskets',
        [
            'complete_url' => $completeUrl,
            'cancel_url' => $cancelUrl,
            'complete_auto_redirect' => true,
            'username' => $serverUsername,
            'custom' => [
                'order_token' => (string) $order['public_token'],
                'minecraft_name' => (string) $order['minecraft_name'],
                'minecraft_platform' => (string) $order['minecraft_platform'],
                'minecraft_server_name' => $serverUsername,
            ],
        ]
    );

    $basket = $createResponse['data'] ?? $createResponse;
    $ident = trim((string) ($basket['ident'] ?? ''));
    $usernameId = trim((string) ($basket['username_id'] ?? ''));

    if ($ident === '') {
        throw new RuntimeException(t('tebex.basket_missing'));
    }

    foreach ($items as $item) {
        $packagePayload = [
            'package_id' => (string) $item['product']['tebex_package_id'],
            'quantity' => (int) $item['quantity'],
        ];

        if ($usernameId !== '') {
            $packagePayload['variable_data'] = ['username_id' => $usernameId];
        }

        $basketResponse = http_request_json(
            'POST',
            'https://headless.tebex.io/api/baskets/' . rawurlencode($ident) . '/packages',
            $packagePayload
        );
        $basket = $basketResponse['data'] ?? $basketResponse;
    }

    if ($couponCode !== '') {
        http_request_json(
            'POST',
            'https://headless.tebex.io/api/accounts/' . $token . '/baskets/' . rawurlencode($ident) . '/coupons',
            ['coupon_code' => $couponCode]
        );
    }

    $refresh = http_request_json(
        'GET',
        'https://headless.tebex.io/api/accounts/' . $token . '/baskets/' . rawurlencode($ident)
    );
    $basket = $refresh['data'] ?? $refresh;
    $checkoutUrl = trim((string) ($basket['links']['checkout'] ?? ''));

    if ($checkoutUrl === '' || !filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException(t('tebex.checkout_url_missing'));
    }

    $totals = tebex_basket_totals($basket);

    return [
        'reference' => $ident,
        'checkout_url' => $checkoutUrl,
        'total_cents' => $totals['total_cents'],
        'currency' => $totals['currency'],
    ];
}

function verify_tebex_webhook_signature(string $rawBody, string $signature): bool
{
    $secret = trim((string) config('tebex.webhook_secret', ''));
    $signature = trim($signature);

    if ($secret === '' || $signature === '') {
        return false;
    }

    $expected = hash_hmac('sha256', hash('sha256', $rawBody), $secret);

    return hash_equals($expected, strtolower($signature));
}

function tebex_webhook_ip_allowed(string $ip): bool
{
    $allowed = config('tebex.allowed_webhook_ips', []);

    if (!is_array($allowed) || $allowed === []) {
        return true;
    }

    foreach ($allowed as $network) {
        if (is_string($network) && ip_matches_network($ip, $network)) {
            return true;
        }
    }

    return false;
}

function tebex_webhook_products(array $subject): ?array
{
    $products = $subject['products'] ?? null;

    if (!is_array($products) || $products === []) {
        return null;
    }

    $normalized = [];

    foreach ($products as $product) {
        if (!is_array($product)) {
            return null;
        }

        $packageId = trim((string) (
            $product['id']
            ?? $product['package_id']
            ?? ($product['package']['id'] ?? '')
        ));
        $quantity = filter_var($product['quantity'] ?? 1, FILTER_VALIDATE_INT);

        if ($packageId === '' || $quantity === false || (int) $quantity < 1) {
            return null;
        }

        $normalized[$packageId] = ($normalized[$packageId] ?? 0) + (int) $quantity;
    }

    ksort($normalized);

    return $normalized;
}

function tebex_expected_order_products(array $order): ?array
{
    $items = $order['items'] ?? null;

    if (!is_array($items) || $items === []) {
        return null;
    }

    $expected = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            return null;
        }

        $packageId = trim((string) ($item['tebex_package_id'] ?? ''));
        $quantity = (int) ($item['quantity'] ?? 0);

        if ($packageId === '' || $quantity < 1) {
            return null;
        }

        $expected[$packageId] = ($expected[$packageId] ?? 0) + $quantity;
    }

    ksort($expected);

    return $expected;
}

function tebex_webhook_amount(array $subject): ?array
{
    $price = is_array($subject['price_paid'] ?? null)
        ? $subject['price_paid']
        : (is_array($subject['price'] ?? null) ? $subject['price'] : null);

    if ($price === null) {
        return null;
    }

    $amount = tebex_money_to_cents($price['amount'] ?? null);
    $currency = strtoupper(trim((string) ($price['currency'] ?? '')));

    if ($amount === null || !preg_match('/^[A-Z]{3}$/', $currency)) {
        return null;
    }

    return [
        'total_cents' => $amount,
        'currency' => $currency,
    ];
}

function tebex_webhook_matches_order(array $subject, array $order): bool
{
    $expectedProducts = tebex_expected_order_products($order);
    $receivedProducts = tebex_webhook_products($subject);

    if ($expectedProducts === null || $receivedProducts === null || $expectedProducts !== $receivedProducts) {
        return false;
    }

    if (!(bool) config('tebex.verify_webhook_amount', true)) {
        return true;
    }

    $receivedAmount = tebex_webhook_amount($subject);

    if ($receivedAmount === null) {
        return false;
    }

    $expectedCents = isset($order['tebex_total_cents']) && $order['tebex_total_cents'] !== null
        ? (int) $order['tebex_total_cents']
        : (int) $order['total_cents'];
    $expectedCurrency = trim((string) ($order['tebex_currency'] ?? ''));

    if ($expectedCurrency === '') {
        $expectedCurrency = (string) ($order['currency'] ?? '');
    }

    return $receivedAmount['total_cents'] === $expectedCents
        && $receivedAmount['currency'] === strtoupper($expectedCurrency);
}
