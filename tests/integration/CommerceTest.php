<?php

declare(strict_types=1);

    $now = date('Y-m-d H:i:s');
    $insertCoupon = $pdo->prepare(
        'INSERT INTO coupons
         (code, discount_type, discount_value, min_subtotal_cents, max_uses, used_count, active, expires_at, created_at, updated_at)
         VALUES
         (:code, :discount_type, :discount_value, 0, :max_uses, :used_count, 1, :expires_at, :created_at, :updated_at)'
    );
    $insertCoupon->execute([
        'code' => 'EXPIRED',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'max_uses' => null,
        'used_count' => 0,
        'expires_at' => date('Y-m-d H:i:s', time() - 60),
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $insertCoupon->execute([
        'code' => 'EXHAUSTED',
        'discount_type' => 'fixed',
        'discount_value' => 100,
        'max_uses' => 1,
        'used_count' => 1,
        'expires_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $throws(static fn () => validate_coupon('EXPIRED', 2000), 'Expired coupons are rejected.');
    $throws(static fn () => validate_coupon('EXHAUSTED', 2000), 'Exhausted coupons are rejected.');
    $expiredCoupons = all_coupons_admin([
        'search' => '',
        'type' => '',
        'state' => 'expired',
        'sort' => 'code_asc',
    ]);
    $exhaustedCoupons = all_coupons_admin([
        'search' => '',
        'type' => '',
        'state' => 'exhausted',
        'sort' => 'code_asc',
    ]);
    $assert(
        in_array('EXPIRED', array_column($expiredCoupons, 'code'), true),
        'Coupon AJAX filters can select expired coupons.'
    );
    $assert(
        in_array('EXHAUSTED', array_column($exhaustedCoupons, 'code'), true),
        'Coupon AJAX filters can select exhausted coupons.'
    );
    $assert((int) $pdo->query('SELECT COUNT(*) FROM admin_login_limits')->fetchColumn() === 0, 'The admin rate-limit table is available.');
