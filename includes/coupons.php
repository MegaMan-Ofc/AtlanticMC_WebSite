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
        throw new InvalidArgumentException('Cupão inválido ou inativo.');
    }

    if ($coupon['expires_at'] !== null && strtotime((string) $coupon['expires_at']) < time()) {
        throw new InvalidArgumentException('Este cupão expirou.');
    }

    if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
        throw new InvalidArgumentException('Este cupão já atingiu o limite de utilizações.');
    }

    if ($subtotalCents < (int) $coupon['min_subtotal_cents']) {
        throw new InvalidArgumentException(
            'Este cupão exige um subtotal mínimo de ' . format_money((int) $coupon['min_subtotal_cents']) . '.'
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

function all_coupons_admin(): array
{
    return db()->query('SELECT * FROM coupons ORDER BY created_at DESC, id DESC')->fetchAll();
}

function save_coupon_from_admin(array $input): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $code = strtoupper(trim((string) ($input['code'] ?? '')));
    $type = (string) ($input['discount_type'] ?? 'percentage');
    $rawValue = trim((string) ($input['discount_value'] ?? '0'));
    $value = $type === 'fixed'
        ? parse_money_to_cents($rawValue, 'Coupon value')
        : filter_var($rawValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
    $minimum = parse_money_to_cents((string) ($input['min_subtotal'] ?? '0'), 'Minimum subtotal');
    $maxUses = trim((string) ($input['max_uses'] ?? ''));
    $expiresAt = trim((string) ($input['expires_at'] ?? ''));
    $active = isset($input['active']) ? 1 : 0;

    if (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
        throw new InvalidArgumentException('Coupon code must contain 3 to 50 letters, numbers, underscores or hyphens.');
    }

    if (!in_array($type, ['percentage', 'fixed'], true)) {
        throw new InvalidArgumentException('Invalid coupon type.');
    }

    if (($type === 'percentage' && $value === false) || ($type === 'fixed' && $value < 1)) {
        throw new InvalidArgumentException('Invalid coupon value.');
    }

    $maximumUses = null;

    if ($maxUses !== '') {
        $maximumUses = filter_var($maxUses, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($maximumUses === false) {
            throw new InvalidArgumentException('Maximum uses must be a positive integer.');
        }
    }

    $parameters = [
        'code' => $code,
        'discount_type' => $type,
        'discount_value' => (int) $value,
        'min_subtotal_cents' => max(0, $minimum),
        'max_uses' => $maximumUses,
        'active' => $active,
        'expires_at' => parse_optional_datetime($expiresAt, 'Coupon expiry'),
        'updated_at' => now_sql(),
    ];

    if ($id > 0) {
        $parameters['id'] = $id;
        $statement = db()->prepare(
            'UPDATE coupons SET code = :code, discount_type = :discount_type, discount_value = :discount_value,
             min_subtotal_cents = :min_subtotal_cents, max_uses = :max_uses, active = :active,
             expires_at = :expires_at, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute($parameters);

        return $id;
    }

    $parameters['created_at'] = now_sql();
    $statement = db()->prepare(
        'INSERT INTO coupons
         (code, discount_type, discount_value, min_subtotal_cents, max_uses, used_count, active, expires_at, created_at, updated_at)
         VALUES (:code, :discount_type, :discount_value, :min_subtotal_cents, :max_uses, 0, :active, :expires_at, :created_at, :updated_at)'
    );
    $statement->execute($parameters);

    return (int) db()->lastInsertId();
}
