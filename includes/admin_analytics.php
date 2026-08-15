<?php

declare(strict_types=1);

function admin_analytics_period(int $days = 30): array
{
    $days = max(7, min(365, $days));
    $start = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days');
    $end = (new DateTimeImmutable('tomorrow'));

    return [
        'days' => $days,
        'start_date' => $start->format('Y-m-d'),
        'start' => $start->format('Y-m-d 00:00:00'),
        'end' => $end->format('Y-m-d 00:00:00'),
    ];
}

function admin_analytics_percent(int|float $part, int|float $total): float
{
    if ((float) $total <= 0.0) {
        return 0.0;
    }

    return round(((float) $part / (float) $total) * 100, 1);
}

function admin_analytics_traffic_totals(int $days): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT COALESCE(SUM(page_views), 0) AS page_views,
                COALESCE(SUM(unique_sessions), 0) AS unique_sessions
         FROM daily_site_stats
         WHERE visit_date >= :start_date'
    );
    $statement->execute(['start_date' => $period['start_date']]);
    $row = $statement->fetch() ?: [];

    return [
        'page_views' => (int) ($row['page_views'] ?? 0),
        'unique_sessions' => (int) ($row['unique_sessions'] ?? 0),
    ];
}

function admin_analytics_sales_totals(int $days): array
{
    $period = admin_analytics_period($days);
    $params = [
        'status' => 'paid',
        'start' => $period['start'],
        'end' => $period['end'],
    ];
    $statement = db()->prepare(
        'SELECT COUNT(*) AS paid_orders,
                COALESCE(SUM(total_cents), 0) AS revenue_cents,
                COALESCE(AVG(total_cents), 0) AS average_order_cents,
                COALESCE(SUM(discount_cents), 0) AS discount_cents
         FROM orders
         WHERE status = :status
           AND COALESCE(paid_at, updated_at) >= :start
           AND COALESCE(paid_at, updated_at) < :end'
    );
    $statement->execute($params);
    $row = $statement->fetch() ?: [];

    $itemsStatement = db()->prepare(
        'SELECT COALESCE(SUM(oi.quantity), 0)
         FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE o.status = :status
           AND COALESCE(o.paid_at, o.updated_at) >= :start
           AND COALESCE(o.paid_at, o.updated_at) < :end'
    );
    $itemsStatement->execute($params);

    return [
        'paid_orders' => (int) ($row['paid_orders'] ?? 0),
        'revenue_cents' => (int) ($row['revenue_cents'] ?? 0),
        'average_order_cents' => (int) round((float) ($row['average_order_cents'] ?? 0)),
        'discount_cents' => (int) ($row['discount_cents'] ?? 0),
        'items_sold' => (int) $itemsStatement->fetchColumn(),
    ];
}

function admin_analytics_daily_sales(int $days): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT DATE(COALESCE(paid_at, updated_at)) AS sale_date,
                COUNT(*) AS paid_orders,
                COALESCE(SUM(total_cents), 0) AS revenue_cents
         FROM orders
         WHERE status = :status
           AND COALESCE(paid_at, updated_at) >= :start
           AND COALESCE(paid_at, updated_at) < :end
         GROUP BY DATE(COALESCE(paid_at, updated_at))
         ORDER BY sale_date ASC'
    );
    $statement->execute([
        'status' => 'paid',
        'start' => $period['start'],
        'end' => $period['end'],
    ]);
    $indexed = [];

    foreach ($statement->fetchAll() as $row) {
        $indexed[(string) $row['sale_date']] = $row;
    }

    $stats = [];
    $date = new DateTimeImmutable($period['start_date']);
    $today = new DateTimeImmutable('today');

    for (; $date <= $today; $date = $date->modify('+1 day')) {
        $key = $date->format('Y-m-d');
        $row = $indexed[$key] ?? [];
        $stats[] = [
            'sale_date' => $key,
            'paid_orders' => (int) ($row['paid_orders'] ?? 0),
            'revenue_cents' => (int) ($row['revenue_cents'] ?? 0),
        ];
    }

    return $stats;
}

function admin_analytics_product_rows(int $days): array
{
    $period = admin_analytics_period($days);
    $products = db()->query(
        'SELECT p.id, p.name, p.slug, p.category_id, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         ORDER BY p.id ASC'
    )->fetchAll();
    $rows = [];

    foreach ($products as $product) {
        $id = (int) $product['id'];
        $rows[$id] = [
            'product_id' => $id,
            'name' => (string) $product['name'],
            'slug' => (string) $product['slug'],
            'category_id' => (int) $product['category_id'],
            'category_name' => (string) $product['category_name'],
            'category_slug' => (string) $product['category_slug'],
            'impressions' => 0,
            'unique_sessions' => 0,
            'cart_additions' => 0,
            'cart_sessions' => 0,
            'sold_quantity' => 0,
            'paid_orders' => 0,
            'revenue_cents' => 0,
            'view_to_cart_rate' => 0.0,
            'view_to_sale_rate' => 0.0,
        ];
    }

    if ($rows === []) {
        return [];
    }

    $viewStatement = db()->prepare(
        'SELECT product_id,
                COALESCE(SUM(impressions), 0) AS impressions,
                COALESCE(SUM(unique_sessions), 0) AS unique_sessions,
                COALESCE(SUM(cart_additions), 0) AS cart_additions,
                COALESCE(SUM(cart_sessions), 0) AS cart_sessions
         FROM daily_product_stats
         WHERE visit_date >= :start_date
         GROUP BY product_id'
    );
    $viewStatement->execute(['start_date' => $period['start_date']]);

    foreach ($viewStatement->fetchAll() as $row) {
        $id = (int) $row['product_id'];

        if (!isset($rows[$id])) {
            continue;
        }

        $rows[$id]['impressions'] = (int) $row['impressions'];
        $rows[$id]['unique_sessions'] = (int) $row['unique_sessions'];
        $rows[$id]['cart_additions'] = (int) $row['cart_additions'];
        $rows[$id]['cart_sessions'] = (int) $row['cart_sessions'];
    }

    $salesStatement = db()->prepare(
        'SELECT oi.product_id,
                COALESCE(SUM(oi.quantity), 0) AS sold_quantity,
                COUNT(DISTINCT oi.order_id) AS paid_orders,
                COALESCE(SUM(oi.line_total_cents), 0) AS revenue_cents
         FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE o.status = :status
           AND COALESCE(o.paid_at, o.updated_at) >= :start
           AND COALESCE(o.paid_at, o.updated_at) < :end
         GROUP BY oi.product_id'
    );
    $salesStatement->execute([
        'status' => 'paid',
        'start' => $period['start'],
        'end' => $period['end'],
    ]);

    foreach ($salesStatement->fetchAll() as $row) {
        $id = (int) $row['product_id'];

        if (!isset($rows[$id])) {
            continue;
        }

        $rows[$id]['sold_quantity'] = (int) $row['sold_quantity'];
        $rows[$id]['paid_orders'] = (int) $row['paid_orders'];
        $rows[$id]['revenue_cents'] = (int) $row['revenue_cents'];
    }

    foreach ($rows as &$row) {
        $row['view_to_cart_rate'] = admin_analytics_percent($row['cart_sessions'], $row['unique_sessions']);
        $row['view_to_sale_rate'] = admin_analytics_percent($row['sold_quantity'], $row['impressions']);
    }
    unset($row);

    return array_values($rows);
}

function admin_analytics_rank_products(array $rows, string $metric, int $limit = 5): array
{
    $rows = array_values(array_filter(
        $rows,
        static fn (array $row): bool => (float) ($row[$metric] ?? 0) > 0
    ));

    usort($rows, static function (array $left, array $right) use ($metric): int {
        $comparison = ($right[$metric] ?? 0) <=> ($left[$metric] ?? 0);

        return $comparison !== 0 ? $comparison : strcasecmp((string) $left['name'], (string) $right['name']);
    });

    return array_slice($rows, 0, max(1, $limit));
}

function admin_analytics_category_rows(int $days, array $productRows): array
{
    $period = admin_analytics_period($days);
    $categories = all_store_categories(true);
    $rows = [];

    foreach ($categories as $category) {
        $id = (int) $category['id'];
        $rows[$id] = [
            'category_id' => $id,
            'name' => (string) $category['name'],
            'slug' => (string) $category['slug'],
            'page_views' => 0,
            'unique_sessions' => 0,
            'impressions' => 0,
            'cart_additions' => 0,
            'sold_quantity' => 0,
            'revenue_cents' => 0,
        ];
    }

    foreach ($productRows as $product) {
        $id = (int) $product['category_id'];

        if (!isset($rows[$id])) {
            continue;
        }

        $rows[$id]['impressions'] += (int) $product['impressions'];
        $rows[$id]['cart_additions'] += (int) $product['cart_additions'];
        $rows[$id]['sold_quantity'] += (int) $product['sold_quantity'];
        $rows[$id]['revenue_cents'] += (int) $product['revenue_cents'];
    }

    $routeStatement = db()->prepare(
        'SELECT route_key,
                COALESCE(SUM(page_views), 0) AS page_views,
                COALESCE(SUM(unique_sessions), 0) AS unique_sessions
         FROM daily_route_stats
         WHERE visit_date >= :start_date
           AND route_key LIKE :category_prefix
         GROUP BY route_key'
    );
    $routeStatement->execute([
        'start_date' => $period['start_date'],
        'category_prefix' => 'category:%',
    ]);

    $slugToId = [];

    foreach ($rows as $id => $row) {
        $slugToId[$row['slug']] = $id;
    }

    foreach ($routeStatement->fetchAll() as $row) {
        $routeKey = (string) $row['route_key'];
        $slug = str_starts_with($routeKey, 'category:') ? substr($routeKey, 9) : '';
        $id = $slugToId[$slug] ?? null;

        if ($id === null || !isset($rows[$id])) {
            continue;
        }

        $rows[$id]['page_views'] = (int) $row['page_views'];
        $rows[$id]['unique_sessions'] = (int) $row['unique_sessions'];
    }

    $result = array_values($rows);
    usort($result, static fn (array $a, array $b): int => $b['revenue_cents'] <=> $a['revenue_cents']);

    return $result;
}

function admin_analytics_route_label(string $routeKey): string
{
    if (str_starts_with($routeKey, 'category:')) {
        $slug = substr($routeKey, 9);
        $category = store_category_by_slug($slug, true);

        return $category === null
            ? t('admin.analytics_route_category', ['name' => ucfirst($slug)])
            : t('admin.analytics_route_category', ['name' => (string) $category['name']]);
    }

    return match ($routeKey) {
        'home' => t('admin.analytics_route_home'),
        'search' => t('admin.analytics_route_search'),
        'cart' => t('admin.analytics_route_cart'),
        'checkout' => t('admin.analytics_route_checkout'),
        'login' => t('admin.analytics_route_login'),
        'faq' => t('admin.analytics_route_faq'),
        'privacy' => t('admin.analytics_route_privacy'),
        'terms' => t('admin.analytics_route_terms'),
        'purchase-policy' => t('admin.analytics_route_purchase_policy'),
        'rules' => t('admin.analytics_route_rules'),
        default => ucfirst(str_replace(['-', '_'], ' ', $routeKey)),
    };
}

function admin_analytics_top_pages(int $days, int $limit = 6): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT route_key,
                COALESCE(SUM(page_views), 0) AS page_views,
                COALESCE(SUM(unique_sessions), 0) AS unique_sessions
         FROM daily_route_stats
         WHERE visit_date >= :start_date
         GROUP BY route_key
         ORDER BY page_views DESC, unique_sessions DESC
         LIMIT ' . max(1, min(20, $limit))
    );
    $statement->execute(['start_date' => $period['start_date']]);
    $rows = [];

    foreach ($statement->fetchAll() as $row) {
        $rows[] = [
            'route_key' => (string) $row['route_key'],
            'label' => admin_analytics_route_label((string) $row['route_key']),
            'page_views' => (int) $row['page_views'],
            'unique_sessions' => (int) $row['unique_sessions'],
        ];
    }

    return $rows;
}

function admin_analytics_order_statuses(int $days): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT status, COUNT(*) AS total
         FROM orders
         WHERE created_at >= :start AND created_at < :end
         GROUP BY status
         ORDER BY total DESC'
    );
    $statement->execute(['start' => $period['start'], 'end' => $period['end']]);
    $rows = [];

    foreach ($statement->fetchAll() as $row) {
        $rows[] = ['status' => (string) $row['status'], 'total' => (int) $row['total']];
    }

    return $rows;
}

function admin_analytics_platforms(int $days): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT minecraft_platform AS platform, COUNT(*) AS total
         FROM orders
         WHERE status = :status
           AND COALESCE(paid_at, updated_at) >= :start
           AND COALESCE(paid_at, updated_at) < :end
         GROUP BY minecraft_platform
         ORDER BY total DESC'
    );
    $statement->execute(['status' => 'paid', 'start' => $period['start'], 'end' => $period['end']]);

    return array_map(static fn (array $row): array => [
        'platform' => (string) $row['platform'],
        'total' => (int) $row['total'],
    ], $statement->fetchAll());
}

function admin_analytics_coupon_rows(int $days, int $limit = 5): array
{
    $period = admin_analytics_period($days);
    $statement = db()->prepare(
        'SELECT coupon_code,
                COUNT(*) AS uses,
                COALESCE(SUM(discount_cents), 0) AS discount_cents
         FROM orders
         WHERE status = :status
           AND coupon_code IS NOT NULL
           AND coupon_code <> :empty_code
           AND COALESCE(paid_at, updated_at) >= :start
           AND COALESCE(paid_at, updated_at) < :end
         GROUP BY coupon_code
         ORDER BY uses DESC, discount_cents DESC
         LIMIT ' . max(1, min(20, $limit))
    );
    $statement->execute([
        'status' => 'paid',
        'empty_code' => '',
        'start' => $period['start'],
        'end' => $period['end'],
    ]);

    return array_map(static fn (array $row): array => [
        'code' => (string) $row['coupon_code'],
        'uses' => (int) $row['uses'],
        'discount_cents' => (int) $row['discount_cents'],
    ], $statement->fetchAll());
}

function admin_dashboard_analytics(int $days = 30): array
{
    $days = max(7, min(365, $days));
    $traffic = admin_analytics_traffic_totals($days);
    $sales = admin_analytics_sales_totals($days);
    $products = admin_analytics_product_rows($days);
    $categories = admin_analytics_category_rows($days, $products);
    $impressions = array_sum(array_column($products, 'impressions'));
    $cartAdditions = array_sum(array_column($products, 'cart_additions'));
    $soldQuantity = array_sum(array_column($products, 'sold_quantity'));

    return [
        'days' => $days,
        'traffic_totals' => $traffic,
        'sales_totals' => $sales,
        'daily_sales' => admin_analytics_daily_sales($days),
        'top_selling_products' => admin_analytics_rank_products($products, 'sold_quantity'),
        'top_revenue_products' => admin_analytics_rank_products($products, 'revenue_cents'),
        'top_viewed_products' => admin_analytics_rank_products($products, 'impressions'),
        'top_cart_products' => admin_analytics_rank_products($products, 'cart_additions'),
        'best_conversion_products' => array_slice(array_values(array_filter(
            admin_analytics_rank_products($products, 'view_to_cart_rate', 20),
            static fn (array $row): bool => (int) $row['impressions'] > 0
        )), 0, 5),
        'categories' => $categories,
        'top_pages' => admin_analytics_top_pages($days),
        'order_statuses' => admin_analytics_order_statuses($days),
        'platforms' => admin_analytics_platforms($days),
        'coupons' => admin_analytics_coupon_rows($days),
        'funnel' => [
            'impressions' => $impressions,
            'cart_additions' => $cartAdditions,
            'sold_quantity' => $soldQuantity,
            'view_to_cart_rate' => admin_analytics_percent($cartAdditions, $impressions),
            'cart_to_sale_rate' => admin_analytics_percent($soldQuantity, $cartAdditions),
        ],
        'site_conversion_rate' => admin_analytics_percent($sales['paid_orders'], $traffic['unique_sessions']),
    ];
}
