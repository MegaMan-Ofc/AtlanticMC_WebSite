<?php

declare(strict_types=1);

function tebex_is_configured(): bool
{
    return trim((string) config('tebex.public_token', '')) !== '';
}

function tebex_create_checkout(array $order, array $items): array
{
    if (!tebex_is_configured()) {
        throw new RuntimeException('Tebex is not configured.');
    }

    foreach ($items as $item) {
        if (trim((string) ($item['product']['tebex_package_id'] ?? '')) === '') {
            throw new RuntimeException('One or more products do not have a Tebex package ID.');
        }
    }

    $completeUrl = url('success.php?order=' . rawurlencode((string) $order['public_token']));
    $cancelUrl = url('cart.php');

    if (!filter_var($completeUrl, FILTER_VALIDATE_URL) || !filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('APP_URL must contain the full public store URL before Tebex checkout can be used.');
    }

    $token = rawurlencode((string) config('tebex.public_token'));
    $createResponse = http_request_json(
        'POST',
        'https://headless.tebex.io/api/accounts/' . $token . '/baskets',
        [
            'complete_url' => $completeUrl,
            'cancel_url' => $cancelUrl,
            'complete_auto_redirect' => true,
            'username' => (string) $order['minecraft_name'],
            'custom' => [
                'order_token' => (string) $order['public_token'],
                'minecraft_name' => (string) $order['minecraft_name'],
                'minecraft_platform' => (string) $order['minecraft_platform'],
            ],
        ]
    );

    $basket = $createResponse['data'] ?? $createResponse;
    $ident = (string) ($basket['ident'] ?? '');
    $usernameId = trim((string) ($basket['username_id'] ?? ''));

    if ($ident === '') {
        throw new RuntimeException('Tebex did not return a basket identifier.');
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

    $couponCode = trim((string) ($order['coupon_code'] ?? ''));

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
    $checkoutUrl = (string) ($basket['links']['checkout'] ?? '');

    if ($checkoutUrl === '' || !filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Tebex did not return a valid checkout URL.');
    }

    return [
        'reference' => $ident,
        'checkout_url' => $checkoutUrl,
    ];
}

function verify_tebex_webhook_signature(string $rawBody, string $signature): bool
{
    $secret = (string) config('tebex.webhook_secret', '');

    if ($secret === '' || $signature === '') {
        return false;
    }

    $expected = hash_hmac('sha256', hash('sha256', $rawBody), $secret);

    return hash_equals($expected, $signature);
}

function tebex_webhook_ip_allowed(string $ip): bool
{
    $allowed = config('tebex.allowed_webhook_ips', []);

    if (!is_array($allowed) || $allowed === []) {
        return true;
    }

    return in_array($ip, $allowed, true);
}

function tebex_webhook_matches_order(array $subject, array $order): bool
{
    $products = $subject['products'] ?? null;

    if (!is_array($products) || $products === []) {
        return false;
    }

    $expected = [];

    foreach ($order['items'] as $item) {
        $packageId = trim((string) ($item['tebex_package_id'] ?? ''));

        if ($packageId === '') {
            return false;
        }

        $expected[$packageId] = ($expected[$packageId] ?? 0) + (int) $item['quantity'];
    }

    $received = [];

    foreach ($products as $product) {
        if (!is_array($product)) {
            return false;
        }

        $packageId = (string) (
            $product['id']
            ?? $product['package_id']
            ?? ($product['package']['id'] ?? '')
        );
        $quantity = (int) ($product['quantity'] ?? 1);

        if ($packageId === '' || $quantity < 1) {
            return false;
        }

        $received[$packageId] = ($received[$packageId] ?? 0) + $quantity;
    }

    ksort($expected);
    ksort($received);

    if ($expected !== $received) {
        return false;
    }

    if (!(bool) config('tebex.verify_webhook_amount', true)) {
        return true;
    }

    $price = is_array($subject['price_paid'] ?? null)
        ? $subject['price_paid']
        : (is_array($subject['price'] ?? null) ? $subject['price'] : null);

    if ($price === null || !isset($price['amount'], $price['currency'])) {
        return false;
    }

    $receivedCents = (int) round(((float) $price['amount']) * 100);
    $receivedCurrency = strtoupper((string) $price['currency']);

    return $receivedCents === (int) $order['total_cents']
        && $receivedCurrency === strtoupper((string) $order['currency']);
}
