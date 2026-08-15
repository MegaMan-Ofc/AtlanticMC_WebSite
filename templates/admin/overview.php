<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.overview')) ?></h2>
        <p><?= e(t('admin.overview_text')) ?></p>
    </div>
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
    class="admin-analytics-dashboard"
    data-admin-analytics-dashboard
    data-endpoint="<?= e(url('ajax/admin-analytics.php')) ?>"
    aria-live="polite"
>
    <?php require BASE_PATH . '/templates/admin/analytics-dashboard-content.php'; ?>
</section>
