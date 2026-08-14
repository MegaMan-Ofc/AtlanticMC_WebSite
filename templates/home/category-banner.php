<?php
$homeBannerCategory ??= null;
$homeBannerPosition ??= 'top';

if (!is_array($homeBannerCategory)) {
    return;
}
?>
<a
    class="home-category-banner home-category-banner--<?= e($homeBannerPosition) ?>"
    data-theme="<?= e((string) $homeBannerCategory['theme']) ?>"
    href="<?= e((string) $homeBannerCategory['url']) ?>"
>
    <span class="home-category-banner-watermark" aria-hidden="true"><?= e((string) $homeBannerCategory['name']) ?></span>
    <div class="home-category-banner-copy">
        <small><?= e(t('home.category_banner_kicker')) ?></small>
        <strong><?= e((string) $homeBannerCategory['name']) ?></strong>
        <span>
            <?= e(t('home.category_banner_action')) ?>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </span>
    </div>
    <div class="home-category-banner-image">
        <img
            src="<?= e(url((string) $homeBannerCategory['image'])) ?>"
            alt="<?= e(t('home.category_image_alt', ['category' => (string) $homeBannerCategory['name']])) ?>"
        >
    </div>
</a>
