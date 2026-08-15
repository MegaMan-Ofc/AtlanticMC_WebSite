<?php
$adminHomeCategory ??= null;

if (!is_array($adminHomeCategory)) {
    return;
}

$adminHomeBannerSettings = home_banner_settings($adminHomeCategory);
$adminHomeBannerCustomized = home_banner_is_customized($adminHomeCategory);
?>
<article
    class="admin-home-category-card<?= !(bool) $adminHomeCategory['active'] ? ' is-inactive' : '' ?>"
    data-admin-home-category
    data-category-id="<?= (int) $adminHomeCategory['id'] ?>"
    data-category-name="<?= e((string) $adminHomeCategory['name']) ?>"
    data-banner-image="<?= e(url((string) $adminHomeCategory['image'])) ?>"
    data-banner-theme="<?= e(category_card_theme((string) $adminHomeCategory['slug'])) ?>"
    data-banner-kicker="<?= e($adminHomeBannerSettings['kicker']) ?>"
    data-banner-title="<?= e($adminHomeBannerSettings['title']) ?>"
    data-banner-text="<?= e($adminHomeBannerSettings['text']) ?>"
    data-banner-cta="<?= e($adminHomeBannerSettings['cta']) ?>"
    data-banner-style="<?= e($adminHomeBannerSettings['style']) ?>"
    data-banner-image-side="<?= e($adminHomeBannerSettings['image_side']) ?>"
    data-banner-image-size="<?= e($adminHomeBannerSettings['image_size']) ?>"
    data-banner-show-watermark="<?= $adminHomeBannerSettings['show_watermark'] ? '1' : '0' ?>"
    data-banner-show-cta="<?= $adminHomeBannerSettings['show_cta'] ? '1' : '0' ?>"
    draggable="true"
>
    <div class="admin-home-category-card-image">
        <img src="<?= e(url((string) $adminHomeCategory['image'])) ?>" alt="" loading="lazy">
    </div>
    <div class="admin-home-category-card-copy">
        <small><?= e(t('admin.home_category_label')) ?></small>
        <strong><?= e((string) $adminHomeCategory['name']) ?></strong>
        <span class="admin-home-category-customized" data-admin-home-banner-customized <?= $adminHomeBannerCustomized ? '' : 'hidden' ?>>
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
            <?= e(t('admin.home_banner_customized')) ?>
        </span>
        <?php if (!(bool) $adminHomeCategory['active']): ?>
            <span class="admin-home-category-inactive"><?= e(t('admin.home_category_inactive')) ?></span>
        <?php endif; ?>
    </div>
    <span class="admin-home-category-drag" title="<?= e(t('admin.home_category_drag')) ?>">
        <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
    </span>
</article>
