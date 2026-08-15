<?php
$analyticsDays = (int) ($adminAnalytics['days'] ?? 30);
$salesTotals = $adminAnalytics['sales_totals'] ?? [];
$trafficTotals = $adminAnalytics['traffic_totals'] ?? [];
$funnel = $adminAnalytics['funnel'] ?? [];
$dailySales = array_slice($adminAnalytics['daily_sales'] ?? [], -14);
$maxDailyRevenue = max(1, ...array_map(static fn (array $row): int => (int) $row['revenue_cents'], $dailySales));
$maxDailyOrders = max(1, ...array_map(static fn (array $row): int => (int) $row['paid_orders'], $dailySales));
$topSelling = $adminAnalytics['top_selling_products'] ?? [];
$topViewed = $adminAnalytics['top_viewed_products'] ?? [];
$topCart = $adminAnalytics['top_cart_products'] ?? [];
$categoryRows = $adminAnalytics['categories'] ?? [];
$topPages = $adminAnalytics['top_pages'] ?? [];
$orderStatuses = $adminAnalytics['order_statuses'] ?? [];
$platforms = $adminAnalytics['platforms'] ?? [];
$coupons = $adminAnalytics['coupons'] ?? [];
$maxSold = max(1, ...array_map(static fn (array $row): int => (int) $row['sold_quantity'], $topSelling));
$maxViewed = max(1, ...array_map(static fn (array $row): int => (int) $row['impressions'], $topViewed));
$maxCart = max(1, ...array_map(static fn (array $row): int => (int) $row['cart_additions'], $topCart));
$maxCategoryRevenue = max(1, ...array_map(static fn (array $row): int => (int) $row['revenue_cents'], $categoryRows));
$maxPageViews = max(1, ...array_map(static fn (array $row): int => (int) $row['page_views'], $topPages));
$statusTotal = max(1, array_sum(array_column($orderStatuses, 'total')));
$platformTotal = max(1, array_sum(array_column($platforms, 'total')));
?>
<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.overview')) ?></h2>
        <p><?= e(t('admin.overview_text')) ?></p>
    </div>
    <span class="admin-overview-period"><i class="fa-regular fa-calendar" aria-hidden="true"></i><?= e(t('admin.analytics_last_days', ['days' => $analyticsDays])) ?></span>
</section>

<div class="admin-metrics">
    <article class="admin-metric">
        <span><?= e(t('admin.active_products')) ?></span>
        <strong><?= (int) $adminSummary['active_products'] ?></strong>
        <small><?= e(t('admin.of_total_products', ['count' => (int) $adminSummary['total_products']])) ?></small>
    </article>
    <article class="admin-metric">
        <span><?= e(t('admin.pending_orders')) ?></span>
        <strong><?= (int) $adminSummary['pending_orders'] ?></strong>
        <small><?= e(t('admin.pending_orders_hint')) ?></small>
    </article>
    <article class="admin-metric">
        <span><?= e(t('admin.paid_today')) ?></span>
        <strong><?= (int) $adminSummary['paid_today'] ?></strong>
        <small><?= e(format_money((int) $adminSummary['revenue_today'])) ?></small>
    </article>
    <article class="admin-metric">
        <span><?= e(t('admin.sessions_today')) ?></span>
        <strong><?= (int) $adminSummary['unique_sessions_today'] ?></strong>
        <small><?= e(t('admin.page_views_value', ['count' => (int) $adminSummary['page_views_today']])) ?></small>
    </article>
</div>

<section
    class="admin-panel admin-traffic-panel"
    data-admin-traffic-widget
    data-endpoint="<?= e(url('ajax/admin-traffic.php')) ?>"
    aria-live="polite"
>
    <?php $adminTrafficExpanded = false; require BASE_PATH . '/templates/admin/traffic-widget-content.php'; ?>
</section>

<section class="admin-analytics-section" aria-labelledby="admin-sales-title">
    <header class="admin-analytics-section-heading">
        <div>
            <span><?= e(t('admin.analytics_sales_kicker')) ?></span>
            <h2 id="admin-sales-title"><?= e(t('admin.analytics_sales_title')) ?></h2>
            <p><?= e(t('admin.analytics_sales_text', ['days' => $analyticsDays])) ?></p>
        </div>
        <div class="admin-lifetime-revenue">
            <span><?= e(t('admin.analytics_lifetime_revenue')) ?></span>
            <strong><?= e(format_money((int) $adminSummary['total_paid_revenue'])) ?></strong>
        </div>
    </header>

    <div class="admin-analytics-kpis">
        <article><i class="fa-solid fa-coins" aria-hidden="true"></i><span><?= e(t('admin.analytics_revenue')) ?></span><strong><?= e(format_money((int) ($salesTotals['revenue_cents'] ?? 0))) ?></strong></article>
        <article><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><span><?= e(t('admin.analytics_paid_orders')) ?></span><strong><?= (int) ($salesTotals['paid_orders'] ?? 0) ?></strong></article>
        <article><i class="fa-solid fa-receipt" aria-hidden="true"></i><span><?= e(t('admin.analytics_average_order')) ?></span><strong><?= e(format_money((int) ($salesTotals['average_order_cents'] ?? 0))) ?></strong></article>
        <article><i class="fa-solid fa-box-open" aria-hidden="true"></i><span><?= e(t('admin.analytics_items_sold')) ?></span><strong><?= (int) ($salesTotals['items_sold'] ?? 0) ?></strong></article>
        <article><i class="fa-solid fa-bullseye" aria-hidden="true"></i><span><?= e(t('admin.analytics_site_conversion')) ?></span><strong><?= e(number_format((float) ($adminAnalytics['site_conversion_rate'] ?? 0), 1, ',', '.')) ?>%</strong></article>
    </div>

    <div class="admin-analytics-grid admin-analytics-grid--sales">
        <article class="admin-panel admin-analytics-card admin-sales-trend-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-card-kicker"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i><?= e(t('admin.analytics_trend')) ?></span>
                    <h3><?= e(t('admin.analytics_revenue_14_days')) ?></h3>
                </div>
            </div>
            <div class="admin-sales-chart" aria-label="<?= e(t('admin.analytics_revenue_chart_aria')) ?>">
                <?php foreach ($dailySales as $row): ?>
                    <?php
                    $revenueHeight = (int) $row['revenue_cents'] > 0 ? max(4, (int) round(((int) $row['revenue_cents'] / $maxDailyRevenue) * 100)) : 0;
                    $orderHeight = (int) $row['paid_orders'] > 0 ? max(4, (int) round(((int) $row['paid_orders'] / $maxDailyOrders) * 100)) : 0;
                    $timestamp = strtotime((string) $row['sale_date']);
                    ?>
                    <div class="admin-sales-day" title="<?= e(format_admin_date((string) $row['sale_date']) . ' · ' . format_money((int) $row['revenue_cents']) . ' · ' . (int) $row['paid_orders'] . ' ' . t('admin.analytics_orders_short')) ?>">
                        <div class="admin-sales-bar-wrap">
                            <span class="admin-sales-bar" style="--bar-height: <?= $revenueHeight ?>%"></span>
                            <i style="--order-height: <?= $orderHeight ?>%"></i>
                        </div>
                        <small><?= e($timestamp === false ? '' : date('d/m', $timestamp)) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="admin-sales-chart-summary">
                <span><i class="admin-legend-dot admin-legend-dot--revenue"></i><?= e(t('admin.analytics_revenue')) ?></span>
                <span><i class="admin-legend-dot admin-legend-dot--orders"></i><?= e(t('admin.analytics_orders')) ?></span>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card admin-funnel-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-card-kicker"><i class="fa-solid fa-filter" aria-hidden="true"></i><?= e(t('admin.analytics_funnel_kicker')) ?></span>
                    <h3><?= e(t('admin.analytics_funnel_title')) ?></h3>
                </div>
            </div>
            <div class="admin-funnel">
                <div class="admin-funnel-step admin-funnel-step--views">
                    <span><?= e(t('admin.analytics_product_views')) ?></span>
                    <strong><?= (int) ($funnel['impressions'] ?? 0) ?></strong>
                </div>
                <div class="admin-funnel-rate"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i><?= e(number_format((float) ($funnel['view_to_cart_rate'] ?? 0), 1, ',', '.')) ?>%</div>
                <div class="admin-funnel-step admin-funnel-step--cart">
                    <span><?= e(t('admin.analytics_cart_additions')) ?></span>
                    <strong><?= (int) ($funnel['cart_additions'] ?? 0) ?></strong>
                </div>
                <div class="admin-funnel-rate"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i><?= e(number_format((float) ($funnel['cart_to_sale_rate'] ?? 0), 1, ',', '.')) ?>%</div>
                <div class="admin-funnel-step admin-funnel-step--sales">
                    <span><?= e(t('admin.analytics_items_sold')) ?></span>
                    <strong><?= (int) ($funnel['sold_quantity'] ?? 0) ?></strong>
                </div>
            </div>
            <p class="admin-analytics-hint"><?= e(t('admin.analytics_funnel_hint')) ?></p>
        </article>
    </div>
</section>

<section class="admin-analytics-section" aria-labelledby="admin-products-intelligence-title">
    <header class="admin-analytics-section-heading">
        <div>
            <span><?= e(t('admin.analytics_products_kicker')) ?></span>
            <h2 id="admin-products-intelligence-title"><?= e(t('admin.analytics_products_title')) ?></h2>
            <p><?= e(t('admin.analytics_products_text')) ?></p>
        </div>
    </header>

    <div class="admin-analytics-grid admin-analytics-grid--three">
        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-trophy" aria-hidden="true"></i><?= e(t('admin.analytics_top_sales')) ?></span><h3><?= e(t('admin.analytics_most_sold')) ?></h3></div></div>
            <div class="admin-ranked-list">
                <?php if ($topSelling === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_sales_yet')) ?></p><?php endif; ?>
                <?php foreach ($topSelling as $index => $row): ?>
                    <div class="admin-ranked-row">
                        <span class="admin-rank-number"><?= $index + 1 ?></span>
                        <div><strong><?= e((string) $row['name']) ?></strong><small><?= e((string) $row['category_name']) ?> · <?= e(format_money((int) $row['revenue_cents'])) ?></small><span class="admin-rank-track"><i style="--rank-width: <?= max(4, (int) round(((int) $row['sold_quantity'] / $maxSold) * 100)) ?>%"></i></span></div>
                        <b><?= (int) $row['sold_quantity'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-regular fa-eye" aria-hidden="true"></i><?= e(t('admin.analytics_interest')) ?></span><h3><?= e(t('admin.analytics_most_viewed')) ?></h3></div></div>
            <div class="admin-ranked-list">
                <?php if ($topViewed === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_views_yet')) ?></p><?php endif; ?>
                <?php foreach ($topViewed as $index => $row): ?>
                    <div class="admin-ranked-row">
                        <span class="admin-rank-number"><?= $index + 1 ?></span>
                        <div><strong><?= e((string) $row['name']) ?></strong><small><?= e(t('admin.analytics_unique_viewers_value', ['count' => (int) $row['unique_sessions']])) ?></small><span class="admin-rank-track"><i style="--rank-width: <?= max(4, (int) round(((int) $row['impressions'] / $maxViewed) * 100)) ?>%"></i></span></div>
                        <b><?= (int) $row['impressions'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-cart-plus" aria-hidden="true"></i><?= e(t('admin.analytics_purchase_intent')) ?></span><h3><?= e(t('admin.analytics_most_added')) ?></h3></div></div>
            <div class="admin-ranked-list">
                <?php if ($topCart === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_cart_data')) ?></p><?php endif; ?>
                <?php foreach ($topCart as $index => $row): ?>
                    <div class="admin-ranked-row">
                        <span class="admin-rank-number"><?= $index + 1 ?></span>
                        <div><strong><?= e((string) $row['name']) ?></strong><small><?= e(t('admin.analytics_cart_sessions_value', ['count' => (int) $row['cart_sessions']])) ?></small><span class="admin-rank-track"><i style="--rank-width: <?= max(4, (int) round(((int) $row['cart_additions'] / $maxCart) * 100)) ?>%"></i></span></div>
                        <b><?= (int) $row['cart_additions'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</section>

<section class="admin-analytics-section" aria-labelledby="admin-categories-title">
    <header class="admin-analytics-section-heading">
        <div>
            <span><?= e(t('admin.analytics_catalog_kicker')) ?></span>
            <h2 id="admin-categories-title"><?= e(t('admin.analytics_categories_title')) ?></h2>
            <p><?= e(t('admin.analytics_categories_text')) ?></p>
        </div>
    </header>

    <div class="admin-analytics-grid admin-analytics-grid--catalog">
        <article class="admin-panel admin-analytics-card admin-category-performance-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><?= e(t('admin.analytics_category_performance')) ?></span><h3><?= e(t('admin.analytics_sales_by_category')) ?></h3></div></div>
            <div class="admin-category-performance">
                <?php if ($categoryRows === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_category_data')) ?></p><?php endif; ?>
                <?php foreach ($categoryRows as $row): ?>
                    <div class="admin-category-performance-row">
                        <div class="admin-category-performance-name"><strong><?= e((string) $row['name']) ?></strong><small><?= e(t('admin.analytics_category_views_value', ['count' => (int) $row['page_views']])) ?></small></div>
                        <div class="admin-category-performance-track"><i style="--rank-width: <?= (int) $row['revenue_cents'] > 0 ? max(3, (int) round(((int) $row['revenue_cents'] / $maxCategoryRevenue) * 100)) : 0 ?>%"></i></div>
                        <div class="admin-category-performance-value"><strong><?= e(format_money((int) $row['revenue_cents'])) ?></strong><small><?= e(t('admin.analytics_items_value', ['count' => (int) $row['sold_quantity']])) ?></small></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i><?= e(t('admin.analytics_navigation')) ?></span><h3><?= e(t('admin.analytics_top_pages')) ?></h3></div></div>
            <div class="admin-page-ranking">
                <?php if ($topPages === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_page_data')) ?></p><?php endif; ?>
                <?php foreach ($topPages as $row): ?>
                    <div><span><strong><?= e((string) $row['label']) ?></strong><small><?= e(t('admin.analytics_sessions_value', ['count' => (int) $row['unique_sessions']])) ?></small></span><span class="admin-page-ranking-bar"><i style="--rank-width: <?= max(3, (int) round(((int) $row['page_views'] / $maxPageViews) * 100)) ?>%"></i></span><b><?= (int) $row['page_views'] ?></b></div>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</section>

<section class="admin-analytics-section" aria-labelledby="admin-health-title">
    <header class="admin-analytics-section-heading">
        <div>
            <span><?= e(t('admin.analytics_health_kicker')) ?></span>
            <h2 id="admin-health-title"><?= e(t('admin.analytics_health_title')) ?></h2>
            <p><?= e(t('admin.analytics_health_text')) ?></p>
        </div>
    </header>

    <div class="admin-analytics-grid admin-analytics-grid--three">
        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-signal" aria-hidden="true"></i><?= e(t('admin.analytics_order_status')) ?></span><h3><?= e(t('admin.analytics_orders_distribution')) ?></h3></div></div>
            <div class="admin-segmented-bar" aria-hidden="true">
                <?php foreach ($orderStatuses as $row): ?><i class="admin-segment admin-segment--<?= e((string) $row['status']) ?>" style="--segment-width: <?= admin_analytics_percent((int) $row['total'], $statusTotal) ?>%"></i><?php endforeach; ?>
            </div>
            <div class="admin-status-list">
                <?php if ($orderStatuses === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_orders_period')) ?></p><?php endif; ?>
                <?php foreach ($orderStatuses as $row): ?><div><span><i class="admin-status-dot admin-status-dot--<?= e((string) $row['status']) ?>"></i><?= e(t('status.' . $row['status'])) ?></span><strong><?= (int) $row['total'] ?></strong></div><?php endforeach; ?>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-gamepad" aria-hidden="true"></i><?= e(t('admin.analytics_platforms')) ?></span><h3><?= e(t('admin.analytics_paid_platforms')) ?></h3></div></div>
            <div class="admin-platform-list">
                <?php if ($platforms === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_platform_data')) ?></p><?php endif; ?>
                <?php foreach ($platforms as $row): ?>
                    <div><span><i class="fa-solid <?= $row['platform'] === 'bedrock' ? 'fa-mobile-screen' : 'fa-cube' ?>" aria-hidden="true"></i><?= e(ucfirst((string) $row['platform'])) ?></span><strong><?= (int) $row['total'] ?></strong><small><?= e(number_format(admin_analytics_percent((int) $row['total'], $platformTotal), 1, ',', '.')) ?>%</small></div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="admin-panel admin-analytics-card">
            <div class="admin-card-heading"><div><span class="admin-card-kicker"><i class="fa-solid fa-ticket" aria-hidden="true"></i><?= e(t('admin.analytics_coupons')) ?></span><h3><?= e(t('admin.analytics_coupon_usage')) ?></h3></div></div>
            <div class="admin-coupon-analytics-list">
                <?php if ($coupons === []): ?><p class="admin-analytics-empty"><?= e(t('admin.analytics_no_coupon_data')) ?></p><?php endif; ?>
                <?php foreach ($coupons as $row): ?><div><code><?= e((string) $row['code']) ?></code><span><?= e(t('admin.analytics_coupon_uses', ['count' => (int) $row['uses']])) ?></span><strong>-<?= e(format_money((int) $row['discount_cents'])) ?></strong></div><?php endforeach; ?>
            </div>
        </article>
    </div>
</section>

<section class="admin-panel admin-analytics-note">
    <h3><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= e(t('admin.analytics_privacy_title')) ?></h3>
    <p><?= e(t('admin.analytics_privacy_text')) ?></p>
</section>
