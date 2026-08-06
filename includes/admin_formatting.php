<?php

declare(strict_types=1);

function format_admin_coupon_discount(array $coupon): string
{
    if (($coupon['discount_type'] ?? '') === 'fixed') {
        return format_money((int) ($coupon['discount_value'] ?? 0));
    }

    return (int) ($coupon['discount_value'] ?? 0) . '%';
}

function format_admin_datetime(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d/m/Y H:i', $timestamp);
}

function format_admin_date(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d/m/Y', $timestamp);
}
