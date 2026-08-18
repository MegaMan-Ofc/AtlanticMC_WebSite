<section class="admin-section-heading admin-home-layout-heading">
    <div>
        <h2><?= e(t('admin.home_layout_title')) ?></h2>
        <p><?= e(t('admin.home_layout_text')) ?></p>
    </div>

    <span class="admin-recommended-save-state" data-admin-home-layout-state aria-live="polite"></span>
</section>

<section
    class="admin-panel admin-home-layout-panel"
    data-admin-home-layout
    data-save-url="<?= e(url('actions/admin_save_home_categories.php')) ?>"
    data-csrf-token="<?= e(csrf_token()) ?>"
    data-saving-label="<?= e(t('admin.home_layout_saving')) ?>"
    data-saved-label="<?= e(t('admin.home_layout_saved')) ?>"
    data-error-label="<?= e(t('admin.home_layout_save_error')) ?>"
>
    <div class="admin-home-layout-zone-block admin-home-layout-zone-block--banner">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_top_banner')) ?></span>
                <strong><?= e(t('admin.home_top_banner_help')) ?></strong>
            </div>
            <div class="admin-home-layout-zone-actions">
                <button
                    class="admin-home-banner-edit"
                    type="button"
                    data-admin-home-banner-edit
                    data-zone="top"
                    title="<?= e(t('admin.home_banner_edit')) ?>"
                    aria-label="<?= e(t('admin.home_banner_edit')) ?>"
                    <?= is_array($adminHomeCategoryLayout['top']) ? '' : 'disabled' ?>
                >
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="admin-home-banner-zone" data-admin-home-zone="top">
            <?php if (is_array($adminHomeCategoryLayout['top'])): ?>
                <?php
                $adminHomeCategory = $adminHomeCategoryLayout['top'];
                require TEMPLATE_PATH . '/admin/home/category-card.php';
                ?>
            <?php endif; ?>
            <div class="admin-home-zone-empty" data-admin-home-empty>
                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-home-layout-zone-block">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_category_grid')) ?></span>
                <strong><?= e(t('admin.home_category_grid_help')) ?></strong>
            </div>
            <i class="fa-solid fa-grip" aria-hidden="true"></i>
        </header>

        <div class="admin-home-category-grid" data-admin-home-zone="grid">
            <?php foreach ($adminHomeCategoryLayout['grid'] as $category): ?>
                <?php
                $adminHomeCategory = $category;
                require TEMPLATE_PATH . '/admin/home/category-card.php';
                ?>
            <?php endforeach; ?>
            <div class="admin-home-zone-empty admin-home-zone-empty--grid" data-admin-home-empty>
                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                <span><?= e(t('admin.home_grid_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-home-layout-zone-block admin-home-layout-zone-block--banner">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_bottom_banner')) ?></span>
                <strong><?= e(t('admin.home_bottom_banner_help')) ?></strong>
            </div>
            <div class="admin-home-layout-zone-actions">
                <button
                    class="admin-home-banner-edit"
                    type="button"
                    data-admin-home-banner-edit
                    data-zone="bottom"
                    title="<?= e(t('admin.home_banner_edit')) ?>"
                    aria-label="<?= e(t('admin.home_banner_edit')) ?>"
                    <?= is_array($adminHomeCategoryLayout['bottom']) ? '' : 'disabled' ?>
                >
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="admin-home-banner-zone" data-admin-home-zone="bottom">
            <?php if (is_array($adminHomeCategoryLayout['bottom'])): ?>
                <?php
                $adminHomeCategory = $adminHomeCategoryLayout['bottom'];
                require TEMPLATE_PATH . '/admin/home/category-card.php';
                ?>
            <?php endif; ?>
            <div class="admin-home-zone-empty" data-admin-home-empty>
                <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-inline-note admin-home-layout-note">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span><?= e(t('admin.home_layout_note')) ?></span>
    </div>
</section>
