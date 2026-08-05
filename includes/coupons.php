<?php

declare(strict_types=1);

function coupon_by_code(string $code): ?array
{
    $statement = db()->prepare('SELECT * FROM coupons WHERE code = :code');
    $statement->execute(['code' => strtoupper(trim($code))]);
    $coupon = $statement->fetch();

    return is_array($coupon) ? $coupon : null;
}

function validate_coupon(string $code, int $subtotalCents): array
{
    $coupon = coupon_by_code($code);

    if ($coupon === null || !(bool) $coupon['active']) {
        throw new InvalidArgumentException(t('validation.invalid_coupon'));
    }

    if ($coupon['expires_at'] !== null && strtotime((string) $coupon['expires_at']) < time()) {
        throw new InvalidArgumentException(t('validation.expired_coupon'));
    }

    if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
        throw new InvalidArgumentException(t('validation.coupon_limit'));
    }

    if ($subtotalCents < (int) $coupon['min_subtotal_cents']) {
        throw new InvalidArgumentException(
            t('validation.coupon_minimum', ['amount' => format_money((int) $coupon['min_subtotal_cents'])])
        );
    }

    return $coupon;
}

function coupon_discount(array $coupon, int $subtotalCents): int
{
    if ($coupon['discount_type'] === 'percentage') {
        return min($subtotalCents, (int) floor($subtotalCents * ((int) $coupon['discount_value'] / 100)));
    }

    return min($subtotalCents, (int) $coupon['discount_value']);
}
