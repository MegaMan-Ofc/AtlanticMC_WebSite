<?php

declare(strict_types=1);

function analytics_is_probable_bot(): bool
{
    $agent = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    return $agent !== '' && preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit/', $agent) === 1;
}

function track_public_page_view(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET' || analytics_is_probable_bot()) {
        return;
    }

    $route = current_route_name();

    if ($route === null || $route === 'admin') {
        return;
    }

    $date = date('Y-m-d');
    $counted = $_SESSION['analytics_counted_dates'][$date] ?? false;
    $uniqueIncrement = $counted === true ? 0 : 1;
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
            'unique_sessions' => $uniqueIncrement,
            'updated_at' => $updatedAt,
        ]);

        if ($uniqueIncrement === 1) {
            $_SESSION['analytics_counted_dates'][$date] = true;
            $_SESSION['analytics_counted_dates'] = array_slice(
                $_SESSION['analytics_counted_dates'],
                -14,
                null,
                true
            );
        }
    } catch (Throwable $error) {
        error_log('Analytics tracking failed: ' . $error->getMessage());
    }
}

function daily_traffic_stats(int $days = 30): array
{
    $days = max(7, min(365, $days));
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
