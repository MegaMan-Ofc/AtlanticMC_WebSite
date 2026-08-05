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

<div class="admin-overview-grid">
    <section class="admin-panel">
        <h3><?= e(t('admin.quick_actions')) ?></h3>
        <div class="admin-quick-actions">
            <a href="<?= e(admin_section_url('products')) ?>"><i class="fa-solid fa-tags" aria-hidden="true"></i><span><?= e(t('admin.manage_prices')) ?></span></a>
            <a href="<?= e(admin_section_url('orders')) ?>"><i class="fa-solid fa-receipt" aria-hidden="true"></i><span><?= e(t('admin.view_orders')) ?></span></a>
            <a href="<?= e(admin_section_url('analytics')) ?>"><i class="fa-solid fa-chart-column" aria-hidden="true"></i><span><?= e(t('admin.view_analytics')) ?></span></a>
        </div>
    </section>
    <section class="admin-panel">
        <h3><?= e(t('admin.revenue_summary')) ?></h3>
        <div class="admin-revenue-total"><?= e(format_money((int) $adminSummary['total_paid_revenue'])) ?></div>
        <p><?= e(t('admin.revenue_summary_text')) ?></p>
    </section>
</div>
