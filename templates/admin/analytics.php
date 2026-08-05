<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.analytics')) ?></h2>
        <p><?= e(t('admin.analytics_text')) ?></p>
    </div>
</section>

<div class="admin-metrics admin-metrics--compact">
    <article class="admin-metric"><span><?= e(t('admin.sessions_today')) ?></span><strong><?= (int) $adminSummary['unique_sessions_today'] ?></strong></article>
    <article class="admin-metric"><span><?= e(t('admin.page_views_today')) ?></span><strong><?= (int) $adminSummary['page_views_today'] ?></strong></article>
</div>

<section class="admin-panel">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th><?= e(t('admin.date')) ?></th><th><?= e(t('admin.unique_sessions')) ?></th><th><?= e(t('admin.page_views')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($adminTraffic as $day): ?>
                <tr><td><?= e(format_admin_date((string) $day['visit_date'])) ?></td><td><?= (int) $day['unique_sessions'] ?></td><td><?= (int) $day['page_views'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="admin-panel admin-analytics-note">
    <h3><?= e(t('admin.analytics_privacy_title')) ?></h3>
    <p><?= e(t('admin.analytics_privacy_text')) ?></p>
</section>
