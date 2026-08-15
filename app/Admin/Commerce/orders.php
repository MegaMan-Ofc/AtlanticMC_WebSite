<?php

declare(strict_types=1);

const ADMIN_ORDER_STATUSES = [
    'pending',
    'awaiting_payment',
    'checkout_failed',
    'paid',
    'declined',
    'refunded',
    'disputed',
];

function admin_valid_date(string $value): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
}

function admin_order_filters(): array
{
    $player = substr(trim(query_string('player')), 0, 32);
    $status = query_string('status');
    $order = substr(trim(query_string('order')), 0, 64);
    $dateFrom = query_string('date_from');
    $dateTo = query_string('date_to');
    $sort = query_string('sort');
    $page = max(1, query_int('page', 1));

    if ($status !== '' && !in_array($status, ADMIN_ORDER_STATUSES, true)) {
        $status = '';
    }

    if (!admin_valid_date($dateFrom)) {
        $dateFrom = '';
    }

    if (!admin_valid_date($dateTo)) {
        $dateTo = '';
    }

    if (!in_array($sort, [
        '',
        'created_asc',
        'total_desc',
        'total_asc',
        'player_asc',
        'player_desc',
    ], true)) {
        $sort = '';
    }

    return [
        'player' => $player,
        'status' => $status,
        'order' => $order,
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'sort' => $sort,
        'page' => $page,
    ];
}

function admin_orders_query(array $filters): array
{
    $conditions = [];
    $parameters = [];

    if ($filters['player'] !== '') {
        $conditions[] = 'minecraft_name LIKE :player';
        $parameters['player'] = '%' . $filters['player'] . '%';
    }

    if ($filters['status'] !== '') {
        $conditions[] = 'status = :status';
        $parameters['status'] = $filters['status'];
    }

    if ($filters['order'] !== '') {
        $conditions[] = 'public_token LIKE :public_token';
        $parameters['public_token'] = $filters['order'] . '%';
    }

    if ($filters['date_from'] !== '') {
        $conditions[] = 'created_at >= :date_from';
        $parameters['date_from'] = $filters['date_from'] . ' 00:00:00';
    }

    if ($filters['date_to'] !== '') {
        $end = new DateTimeImmutable($filters['date_to']);
        $conditions[] = 'created_at < :date_to';
        $parameters['date_to'] = $end->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
    }

    return [
        'where' => $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions),
        'parameters' => $parameters,
    ];
}

function admin_order_items(array $orderIds): array
{
    $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds), static fn (int $id): bool => $id > 0)));

    if ($orderIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $statement = db()->prepare(
        "SELECT order_id, product_name, unit_price_cents, quantity, line_total_cents
         FROM order_items WHERE order_id IN ($placeholders) ORDER BY id ASC"
    );
    $statement->execute($orderIds);
    $items = [];

    foreach ($statement->fetchAll() as $item) {
        $items[(int) $item['order_id']][] = $item;
    }

    return $items;
}

function admin_order_order_by(array $filters): string
{
    return match ((string) ($filters['sort'] ?? '')) {
        'created_asc' => 'created_at ASC, id ASC',
        'total_desc' => 'total_cents DESC, id DESC',
        'total_asc' => 'total_cents ASC, id ASC',
        'player_asc' => 'minecraft_name ASC, id DESC',
        'player_desc' => 'minecraft_name DESC, id DESC',
        default => 'created_at DESC, id DESC',
    };
}

function admin_orders_page(array $filters, int $perPage = 25): array
{
    $perPage = max(10, min(100, $perPage));
    $query = admin_orders_query($filters);
    $countStatement = db()->prepare('SELECT COUNT(*) FROM orders' . $query['where']);
    $countStatement->execute($query['parameters']);
    $total = (int) $countStatement->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min((int) $filters['page'], $pages);
    $offset = ($page - 1) * $perPage;
    $statement = db()->prepare(
        'SELECT id, public_token, minecraft_name, minecraft_platform, subtotal_cents, discount_cents,
                total_cents, currency, coupon_code, status, provider, provider_reference, created_at, updated_at
         FROM orders' . $query['where'] . ' ORDER BY ' . admin_order_order_by($filters) . ' LIMIT :limit OFFSET :offset'
    );

    foreach ($query['parameters'] as $name => $value) {
        $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }

    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $orders = $statement->fetchAll();
    $items = admin_order_items(array_column($orders, 'id'));

    foreach ($orders as &$order) {
        $order['items'] = $items[(int) $order['id']] ?? [];
    }
    unset($order);

    return [
        'orders' => $orders,
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
        'per_page' => $perPage,
    ];
}

function recent_orders_admin(int $limit = 50): array
{
    $limit = max(1, min(200, $limit));
    $statement = db()->query(
        'SELECT id, public_token, minecraft_name, minecraft_platform, subtotal_cents, discount_cents, total_cents,
                currency, coupon_code, status, provider, provider_reference, created_at, updated_at
         FROM orders ORDER BY id DESC LIMIT ' . $limit
    );

    return $statement->fetchAll();
}

function admin_order_query_parameters(array $filters, array $overrides = []): array
{
    $parameters = array_merge($filters, $overrides);
    unset($parameters['page']);

    return array_filter(
        $parameters,
        static fn (mixed $value): bool => $value !== '' && $value !== null
    );
}
