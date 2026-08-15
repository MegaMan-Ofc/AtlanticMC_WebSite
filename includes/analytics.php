<?php

declare(strict_types=1);

function analytics_is_probable_bot(): bool
{
    $agent = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    return $agent !== '' && preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit/', $agent) === 1;
}

function analytics_route_key(): ?string
{
    $route = current_route_name();

    if ($route === null || $route === 'admin') {
        return null;
    }

    if ($route === 'category') {
        $slug = public_category_slug_from_request_uri((string) ($_SERVER['REQUEST_URI'] ?? '/'));

        if ($slug === null) {
            $querySlug = $_GET['slug'] ?? null;
            $slug = is_string($querySlug) ? public_category_slug_from_path($querySlug) : null;
        }

        return $slug === null ? 'category' : 'category:' . $slug;
    }

    if (in_array($route, legacy_category_slugs(), true)) {
        return 'category:' . $route;
    }

    return $route;
}

function analytics_session_seen(string $bucket, string $key, string $date): bool
{
    $seen = $_SESSION['analytics_seen'][$date][$bucket][$key] ?? false;

    return $seen === true;
}

function analytics_mark_session_seen(string $bucket, string $key, string $date): void
{
    $_SESSION['analytics_seen'][$date][$bucket][$key] = true;

    if (count($_SESSION['analytics_seen'] ?? []) > 14) {
        ksort($_SESSION['analytics_seen']);
        $_SESSION['analytics_seen'] = array_slice($_SESSION['analytics_seen'], -14, null, true);
    }
}

function analytics_upsert_route(string $date, string $routeKey, int $uniqueIncrement): void
{
    $driver = (string) config('database.driver', 'sqlite');
    $updatedAt = now_sql();

    if ($driver === 'mysql') {
        $statement = db()->prepare(
            'INSERT INTO daily_route_stats (visit_date, route_key, page_views, unique_sessions, updated_at)
             VALUES (:visit_date, :route_key, 1, :unique_sessions, :updated_at)
             ON DUPLICATE KEY UPDATE
                page_views = page_views + 1,
                unique_sessions = unique_sessions + VALUES(unique_sessions),
                updated_at = VALUES(updated_at)'
        );
    } else {
        $statement = db()->prepare(
            'INSERT INTO daily_route_stats (visit_date, route_key, page_views, unique_sessions, updated_at)
             VALUES (:visit_date, :route_key, 1, :unique_sessions, :updated_at)
             ON CONFLICT(visit_date, route_key) DO UPDATE SET
                page_views = page_views + 1,
                unique_sessions = unique_sessions + excluded.unique_sessions,
                updated_at = excluded.updated_at'
        );
    }

    $statement->execute([
        'visit_date' => $date,
        'route_key' => $routeKey,
        'unique_sessions' => $uniqueIncrement,
        'updated_at' => $updatedAt,
    ]);
}

function track_public_page_view(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET' || analytics_is_probable_bot()) {
        return;
    }

    $routeKey = analytics_route_key();

    if ($routeKey === null) {
        return;
    }

    $date = date('Y-m-d');
    $siteCounted = analytics_session_seen('site', 'all', $date);
    $routeCounted = analytics_session_seen('route', $routeKey, $date);
    $driver = (string) config('database.driver', 'sqlite');
    $updatedAt = now_sql();

    try {
        if ($driver === 'mysql') {
            $statement = db()->prepare(
                'INSERT INTO daily_site_stats (visit_date, page_views, unique_sessions, updated_at)
                 VALUES (:visit_date, 1, :unique_sessions, :updated_at)
                 ON DUPLICATE KEY UPDATE
                    page_views = page_views + 1,
                    unique_sessions = unique_sessions + VALUES(unique_sessions),
                    updated_at = VALUES(updated_at)'
            );
        } else {
            $statement = db()->prepare(
                'INSERT INTO daily_site_stats (visit_date, page_views, unique_sessions, updated_at)
                 VALUES (:visit_date, 1, :unique_sessions, :updated_at)
                 ON CONFLICT(visit_date) DO UPDATE SET
                    page_views = page_views + 1,
                    unique_sessions = unique_sessions + excluded.unique_sessions,
                    updated_at = excluded.updated_at'
            );
        }

        $statement->execute([
            'visit_date' => $date,
            'unique_sessions' => $siteCounted ? 0 : 1,
            'updated_at' => $updatedAt,
        ]);

        analytics_upsert_route($date, $routeKey, $routeCounted ? 0 : 1);

        if (!$siteCounted) {
            analytics_mark_session_seen('site', 'all', $date);
        }

        if (!$routeCounted) {
            analytics_mark_session_seen('route', $routeKey, $date);
        }
    } catch (Throwable $error) {
        app_log('warning', 'analytics_tracking_failed', ['message' => $error->getMessage()]);
    }
}

function track_product_impressions(array $products): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET' || analytics_is_probable_bot()) {
        return;
    }

    $ids = [];

    foreach ($products as $product) {
        $id = (int) ($product['id'] ?? 0);

        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    if ($ids === []) {
        return;
    }

    $date = date('Y-m-d');
    $driver = (string) config('database.driver', 'sqlite');
    $updatedAt = now_sql();

    try {
        if ($driver === 'mysql') {
            $statement = db()->prepare(
                'INSERT INTO daily_product_stats
                 (visit_date, product_id, impressions, unique_sessions, cart_additions, cart_sessions, updated_at)
                 VALUES (:visit_date, :product_id, 1, :unique_sessions, 0, 0, :updated_at)
                 ON DUPLICATE KEY UPDATE
                    impressions = impressions + 1,
                    unique_sessions = unique_sessions + VALUES(unique_sessions),
                    updated_at = VALUES(updated_at)'
            );
        } else {
            $statement = db()->prepare(
                'INSERT INTO daily_product_stats
                 (visit_date, product_id, impressions, unique_sessions, cart_additions, cart_sessions, updated_at)
                 VALUES (:visit_date, :product_id, 1, :unique_sessions, 0, 0, :updated_at)
                 ON CONFLICT(visit_date, product_id) DO UPDATE SET
                    impressions = impressions + 1,
                    unique_sessions = unique_sessions + excluded.unique_sessions,
                    updated_at = excluded.updated_at'
            );
        }

        foreach ($ids as $id) {
            $counted = analytics_session_seen('product_view', (string) $id, $date);
            $statement->execute([
                'visit_date' => $date,
                'product_id' => $id,
                'unique_sessions' => $counted ? 0 : 1,
                'updated_at' => $updatedAt,
            ]);

            if (!$counted) {
                analytics_mark_session_seen('product_view', (string) $id, $date);
            }
        }
    } catch (Throwable $error) {
        app_log('warning', 'product_impression_tracking_failed', ['message' => $error->getMessage()]);
    }
}

function track_product_cart_add(int $productId, int $quantity): void
{
    if ($productId < 1 || $quantity < 1 || analytics_is_probable_bot()) {
        return;
    }

    $date = date('Y-m-d');
    $counted = analytics_session_seen('product_cart', (string) $productId, $date);
    $driver = (string) config('database.driver', 'sqlite');
    $updatedAt = now_sql();

    try {
        if ($driver === 'mysql') {
            $statement = db()->prepare(
                'INSERT INTO daily_product_stats
                 (visit_date, product_id, impressions, unique_sessions, cart_additions, cart_sessions, updated_at)
                 VALUES (:visit_date, :product_id, 0, 0, :cart_additions, :cart_sessions, :updated_at)
                 ON DUPLICATE KEY UPDATE
                    cart_additions = cart_additions + VALUES(cart_additions),
                    cart_sessions = cart_sessions + VALUES(cart_sessions),
                    updated_at = VALUES(updated_at)'
            );
        } else {
            $statement = db()->prepare(
                'INSERT INTO daily_product_stats
                 (visit_date, product_id, impressions, unique_sessions, cart_additions, cart_sessions, updated_at)
                 VALUES (:visit_date, :product_id, 0, 0, :cart_additions, :cart_sessions, :updated_at)
                 ON CONFLICT(visit_date, product_id) DO UPDATE SET
                    cart_additions = cart_additions + excluded.cart_additions,
                    cart_sessions = cart_sessions + excluded.cart_sessions,
                    updated_at = excluded.updated_at'
            );
        }

        $statement->execute([
            'visit_date' => $date,
            'product_id' => $productId,
            'cart_additions' => $quantity,
            'cart_sessions' => $counted ? 0 : 1,
            'updated_at' => $updatedAt,
        ]);

        if (!$counted) {
            analytics_mark_session_seen('product_cart', (string) $productId, $date);
        }
    } catch (Throwable $error) {
        app_log('warning', 'cart_analytics_tracking_failed', ['message' => $error->getMessage()]);
    }
}

function daily_traffic_stats(int $days = 30): array
{
    $days = max(3, min(365, $days));
    $from = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days');
    $statement = db()->prepare(
        'SELECT visit_date, page_views, unique_sessions
         FROM daily_site_stats WHERE visit_date >= :visit_date ORDER BY visit_date ASC'
    );
    $statement->execute(['visit_date' => $from->format('Y-m-d')]);
    $indexed = [];

    foreach ($statement->fetchAll() as $row) {
        $indexed[(string) $row['visit_date']] = $row;
    }

    $stats = [];

    for ($date = $from; $date <= new DateTimeImmutable('today'); $date = $date->modify('+1 day')) {
        $key = $date->format('Y-m-d');
        $row = $indexed[$key] ?? null;
        $stats[] = [
            'visit_date' => $key,
            'page_views' => (int) ($row['page_views'] ?? 0),
            'unique_sessions' => (int) ($row['unique_sessions'] ?? 0),
        ];
    }

    return array_reverse($stats);
}

function today_traffic_stats(): array
{
    $statement = db()->prepare(
        'SELECT page_views, unique_sessions FROM daily_site_stats WHERE visit_date = :visit_date'
    );
    $statement->execute(['visit_date' => date('Y-m-d')]);
    $row = $statement->fetch();

    return [
        'page_views' => (int) ($row['page_views'] ?? 0),
        'unique_sessions' => (int) ($row['unique_sessions'] ?? 0),
    ];
}
