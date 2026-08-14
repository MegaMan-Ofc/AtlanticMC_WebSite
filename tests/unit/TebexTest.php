<?php

declare(strict_types=1);

$webhookBody = json_encode([
    'id' => 'evt-test',
    'type' => 'validation.webhook',
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$webhookSignature = hash_hmac(
    'sha256',
    hash('sha256', $webhookBody),
    'test-webhook-secret-0123456789'
);
$assert(
    tebex_webhook_is_configured()
        && !tebex_is_configured(),
    'Tebex webhooks can be configured while customer payments remain disabled.'
);
$assert(
    verify_tebex_webhook_signature($webhookBody, $webhookSignature)
        && !verify_tebex_webhook_signature($webhookBody . 'x', $webhookSignature),
    'Tebex webhook signatures are verified against the exact request body.'
);
$assert(
    tebex_money_to_cents('1.38') === 138
        && tebex_money_to_cents(5) === 500
        && tebex_money_to_cents('invalid') === null,
    'Tebex monetary values are normalized safely to integer cents.'
);
$assert(
    tebex_basket_totals([
        'total_price' => 12.34,
        'currency' => 'eur',
    ]) === [
        'total_cents' => 1234,
        'currency' => 'EUR',
    ],
    'Tebex basket totals preserve the provider-calculated total and currency.'
);
$tebexOrderFixture = [
    'total_cents' => 1000,
    'currency' => 'EUR',
    'tebex_total_cents' => 1234,
    'tebex_currency' => 'EUR',
    'items' => [
        ['tebex_package_id' => '100', 'quantity' => 1],
        ['tebex_package_id' => '200', 'quantity' => 2],
    ],
];
$tebexSubjectFixture = [
    'products' => [
        ['id' => 200, 'quantity' => 2],
        ['id' => 100, 'quantity' => 1],
    ],
    'price_paid' => [
        'amount' => '12.34',
        'currency' => 'EUR',
    ],
];
$assert(
    tebex_webhook_matches_order($tebexSubjectFixture, $tebexOrderFixture),
    'Completed Tebex payments must match package IDs, quantities, provider total and currency.'
);
$wrongQuantitySubject = $tebexSubjectFixture;
$wrongQuantitySubject['products'][0]['quantity'] = 1;
$assert(
    !tebex_webhook_matches_order($wrongQuantitySubject, $tebexOrderFixture),
    'Tebex webhook validation rejects incorrect package quantities.'
);
$wrongAmountSubject = $tebexSubjectFixture;
$wrongAmountSubject['price_paid']['amount'] = '12.35';
$assert(
    !tebex_webhook_matches_order($wrongAmountSubject, $tebexOrderFixture),
    'Tebex webhook validation rejects an incorrect provider total.'
);
$wrongCurrencySubject = $tebexSubjectFixture;
$wrongCurrencySubject['price_paid']['currency'] = 'USD';
$assert(
    !tebex_webhook_matches_order($wrongCurrencySubject, $tebexOrderFixture),
    'Tebex webhook validation rejects an incorrect provider currency.'
);

