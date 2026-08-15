<?php

declare(strict_types=1);

const ADMIN_SECTIONS = ['overview', 'categories', 'products', 'recommended', 'coupons', 'orders'];

function admin_section(): string
{
    $section = query_string('section', 'overview');

    return in_array($section, ADMIN_SECTIONS, true) ? $section : 'overview';
}

function admin_section_url(string $section, array $query = []): string
{
    if (!in_array($section, ADMIN_SECTIONS, true)) {
        $section = 'overview';
    }

    return route_url('admin', array_merge(['section' => $section], $query));
}

function redirect_admin(string $section = 'overview', array $query = []): never
{
    header('Location: ' . admin_section_url($section, $query), true, 303);
    exit;
}

function admin_scalar(string $sql, array $parameters = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($parameters);

    return (int) $statement->fetchColumn();
}

function admin_dashboard_summary(): array
{
    $today = date('Y-m-d');
    $traffic = today_traffic_stats();

    return [
        'active_products' => admin_scalar(
            'SELECT COUNT(*) FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.active = 1 AND c.active = 1'
        ),
        'total_products' => admin_scalar(
            'SELECT COUNT(*) FROM products p INNER JOIN categories c ON c.id = p.category_id'
        ),
        'pending_orders' => admin_scalar(
            "SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'awaiting_payment')"
        ),
        'paid_today' => admin_scalar(
            'SELECT COUNT(*) FROM orders WHERE status = :status AND paid_at >= :start AND paid_at < :end',
            [
                'status' => 'paid',
                'start' => $today . ' 00:00:00',
                'end' => (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d') . ' 00:00:00',
            ]
        ),
        'revenue_today' => admin_scalar(
            'SELECT COALESCE(SUM(total_cents), 0) FROM orders WHERE status = :status AND paid_at >= :start AND paid_at < :end',
            [
                'status' => 'paid',
                'start' => $today . ' 00:00:00',
                'end' => (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d') . ' 00:00:00',
            ]
        ),
        'total_paid_revenue' => admin_scalar(
            'SELECT COALESCE(SUM(total_cents), 0) FROM orders WHERE status = :status',
            ['status' => 'paid']
        ),
        'page_views_today' => $traffic['page_views'],
        'unique_sessions_today' => $traffic['unique_sessions'],
    ];
}
