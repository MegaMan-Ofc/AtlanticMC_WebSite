<?php
$trafficRows = array_reverse($adminTraffic);
$trafficExpanded = (bool) ($adminTrafficExpanded ?? false);
$maxViews = max(1, ...array_map(static fn (array $row): int => (int) $row['page_views'], $trafficRows));
$maxSessions = max(1, ...array_map(static fn (array $row): int => (int) $row['unique_sessions'], $trafficRows));
?>
<div class="admin-card-heading admin-traffic-heading">
    <div>
        <span class="admin-card-kicker"><i class="fa-solid fa-chart-column" aria-hidden="true"></i><?= e(t('admin.analytics_traffic_kicker')) ?></span>
        <h3><?= e(t('admin.analytics_traffic_title')) ?></h3>
        <p><?= e(t('admin.analytics_traffic_text')) ?></p>
    </div>
    <div class="admin-chart-legend" aria-label="<?= e(t('admin.analytics_legend')) ?>">
        <span><i class="admin-legend-dot admin-legend-dot--views"></i><?= e(t('admin.page_views')) ?></span>
        <span><i class="admin-legend-dot admin-legend-dot--sessions"></i><?= e(t('admin.unique_sessions')) ?></span>
    </div>
</div>

<div class="admin-traffic-chart<?= $trafficExpanded ? ' is-expanded' : '' ?>" data-admin-traffic-chart>
    <?php foreach ($trafficRows as $row): ?>
        <?php
        $viewHeight = (int) $row['page_views'] > 0 ? max(4, (int) round(((int) $row['page_views'] / $maxViews) * 100)) : 0;
        $sessionHeight = (int) $row['unique_sessions'] > 0 ? max(4, (int) round(((int) $row['unique_sessions'] / $maxSessions) * 100)) : 0;
        $timestamp = strtotime((string) $row['visit_date']);
        ?>
        <div
            class="admin-traffic-day"
            title="<?= e(t('admin.analytics_day_tooltip', [
                'date' => format_admin_date((string) $row['visit_date']),
                'views' => (int) $row['page_views'],
                'sessions' => (int) $row['unique_sessions'],
            ])) ?>"
        >
            <div class="admin-traffic-bars" aria-hidden="true">
                <span class="admin-traffic-bar admin-traffic-bar--views" style="--bar-height: <?= $viewHeight ?>%"></span>
                <span class="admin-traffic-bar admin-traffic-bar--sessions" style="--bar-height: <?= $sessionHeight ?>%"></span>
            </div>
            <strong><?= (int) $row['page_views'] ?></strong>
            <small><?= e($timestamp === false ? (string) $row['visit_date'] : date('d/m', $timestamp)) ?></small>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-traffic-footer">
    <p><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= e(t('admin.analytics_privacy_short')) ?></p>
    <button
        class="button button--ghost admin-traffic-toggle"
        type="button"
        data-admin-traffic-toggle
        data-days="<?= $trafficExpanded ? 7 : 30 ?>"
        aria-expanded="<?= $trafficExpanded ? 'true' : 'false' ?>"
    >
        <span><?= e($trafficExpanded ? t('admin.analytics_show_less') : t('admin.analytics_show_more')) ?></span>
        <i class="fa-solid <?= $trafficExpanded ? 'fa-chevron-up' : 'fa-chevron-down' ?>" aria-hidden="true"></i>
    </button>
</div>
