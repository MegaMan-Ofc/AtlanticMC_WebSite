<?php

declare(strict_types=1);

function admin_coupon_filters(): array
{
    $search = substr(trim(query_string('search')), 0, 50);
    $type = query_string('type');
    $state = query_string('state');
    $sort = query_string('sort');

    if (!in_array($type, ['', 'percentage', 'fixed'], true)) {
        $type = '';
    }

    if (!in_array($state, ['', 'available', 'inactive', 'expired', 'exhausted'], true)) {
        $state = '';
    }

    if (!in_array($sort, [
        '',
        'created_asc',
        'code_asc',
        'code_desc',
        'usage_desc',
        'usage_asc',
    ], true)) {
        $sort = '';
    }

    return [
        'search' => $search,
        'type' => $type,
        'state' => $state,
        'sort' => $sort,
    ];
}

function admin_coupons_query(array $filters): array
{
    $conditions = [];
    $parameters = [];
    $search = (string) ($filters['search'] ?? '');
    $type = (string) ($filters['type'] ?? '');
    $state = (string) ($filters['state'] ?? '');

    if ($search !== '') {
        $conditions[] = 'code LIKE :search';
        $parameters['search'] = '%' . strtoupper($search) . '%';
    }

    if ($type !== '') {
        $conditions[] = 'discount_type = :discount_type';
        $parameters['discount_type'] = $type;
    }

    if ($state === 'available') {
        $conditions[] = 'active = 1';
        $conditions[] = '(expires_at IS NULL OR expires_at > :coupon_now_available)';
        $conditions[] = '(max_uses IS NULL OR used_count < max_uses)';
        $parameters['coupon_now_available'] = now_sql();
    } elseif ($state === 'inactive') {
        $conditions[] = 'active = 0';
    } elseif ($state === 'expired') {
        $conditions[] = 'expires_at IS NOT NULL';
        $conditions[] = 'expires_at <= :coupon_now_expired';
        $parameters['coupon_now_expired'] = now_sql();
    } elseif ($state === 'exhausted') {
        $conditions[] = 'max_uses IS NOT NULL';
        $conditions[] = 'used_count >= max_uses';
    }

    return [
        'where' => $conditions === []
            ? ''
            : ' WHERE ' . implode(' AND ', $conditions),
        'parameters' => $parameters,
    ];
}

function admin_coupon_order_by(array $filters): string
{
    return match ((string) ($filters['sort'] ?? '')) {
        'created_asc' => 'created_at ASC, id ASC',
        'code_asc' => 'code ASC, id ASC',
        'code_desc' => 'code DESC, id DESC',
        'usage_desc' => 'used_count DESC, id DESC',
        'usage_asc' => 'used_count ASC, id ASC',
        default => 'created_at DESC, id DESC',
    };
}

function admin_coupon_query_parameters(array $filters): array
{
    return array_filter(
        [
            'search' => (string) ($filters['search'] ?? ''),
            'type' => (string) ($filters['type'] ?? ''),
            'state' => (string) ($filters['state'] ?? ''),
            'sort' => (string) ($filters['sort'] ?? ''),
        ],
        static fn (string $value): bool => $value !== ''
    );
}

function all_coupons_admin(array $filters = []): array
{
    $query = admin_coupons_query($filters);
    $statement = db()->prepare(
        'SELECT * FROM coupons'
        . $query['where']
        . ' ORDER BY ' . admin_coupon_order_by($filters)
    );
    $statement->execute($query['parameters']);

    return $statement->fetchAll();
}

function save_coupon_from_admin(array $input): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $code = strtoupper(trim((string) ($input['code'] ?? '')));
    $type = (string) ($input['discount_type'] ?? 'percentage');
    $rawValue = trim((string) ($input['discount_value'] ?? '0'));
    $value = $type === 'fixed'
        ? parse_money_to_cents($rawValue, t('field.coupon_value'))
        : filter_var($rawValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
    $minimum = parse_money_to_cents((string) ($input['min_subtotal'] ?? '0'), t('field.minimum_subtotal'));
    $maxUses = trim((string) ($input['max_uses'] ?? ''));
    $expiresAt = trim((string) ($input['expires_at'] ?? ''));
    $active = isset($input['active']) ? 1 : 0;

    if (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
        throw new InvalidArgumentException(t('validation.coupon_code'));
    }

    if (!in_array($type, ['percentage', 'fixed'], true)) {
        throw new InvalidArgumentException(t('validation.coupon_type'));
    }

    if (($type === 'percentage' && $value === false) || ($type === 'fixed' && $value < 1)) {
        throw new InvalidArgumentException(t('validation.coupon_value'));
    }

    $maximumUses = null;

    if ($maxUses !== '') {
        $maximumUses = filter_var($maxUses, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($maximumUses === false) {
            throw new InvalidArgumentException(t('validation.coupon_max_uses'));
        }
    }

    $parameters = [
        'code' => $code,
        'discount_type' => $type,
        'discount_value' => (int) $value,
        'min_subtotal_cents' => max(0, $minimum),
        'max_uses' => $maximumUses,
        'active' => $active,
        'expires_at' => parse_optional_datetime($expiresAt, t('field.coupon_expiry')),
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

function delete_coupon_from_admin(int $id): void
{
    if ($id < 1) {
        throw new InvalidArgumentException(t('validation.coupon_not_found'));
    }

    $statement = db()->prepare('DELETE FROM coupons WHERE id = :id');
    $statement->execute(['id' => $id]);

    if ($statement->rowCount() === 0) {
        throw new InvalidArgumentException(t('validation.coupon_not_found'));
    }
}
