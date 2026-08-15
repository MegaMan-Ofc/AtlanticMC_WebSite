<?php
$homeBannerCategory ??= null;
$homeBannerPosition ??= 'top';

if (!is_array($homeBannerCategory)) {
    return;
}

$homeBannerSettings = is_array($homeBannerCategory['banner'] ?? null)
    ? $homeBannerCategory['banner']
    : home_banner_settings($homeBannerCategory);
$homeBannerKicker = $homeBannerSettings['kicker'] !== ''
    ? $homeBannerSettings['kicker']
    : t('home.category_banner_kicker');
$homeBannerTitle = $homeBannerSettings['title'] !== ''
    ? $homeBannerSettings['title']
    : (string) $homeBannerCategory['name'];
$homeBannerCta = $homeBannerSettings['cta'] !== ''
    ? $homeBannerSettings['cta']
    : t('home.category_banner_action');
?>
<a
    class="home-category-banner home-category-banner--<?= e($homeBannerPosition) ?> home-category-banner--image-<?= e($homeBannerSettings['image_side']) ?> home-category-banner--image-<?= e($homeBannerSettings['image_size']) ?>"
    data-theme="<?= e((string) $homeBannerCategory['theme']) ?>"
    data-banner-style="<?= e($homeBannerSettings['style']) ?>"
    href="<?= e((string) $homeBannerCategory['url']) ?>"
>
    <?php if ($homeBannerSettings['show_watermark']): ?>
        <span class="home-category-banner-watermark" aria-hidden="true"><?= e($homeBannerTitle) ?></span>
    <?php endif; ?>
    <div class="home-category-banner-copy">
        <small><?= e($homeBannerKicker) ?></small>
        <strong><?= e($homeBannerTitle) ?></strong>
        <?php if ($homeBannerSettings['text'] !== ''): ?>
            <p><?= e($homeBannerSettings['text']) ?></p>
        <?php endif; ?>
        <?php if ($homeBannerSettings['show_cta']): ?>
            <span>
                <?= e($homeBannerCta) ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        <?php endif; ?>
    </div>
    <div class="home-category-banner-image">
        <img
            src="<?= e(url((string) $homeBannerCategory['image'])) ?>"
            alt="<?= e(t('home.category_image_alt', ['category' => (string) $homeBannerCategory['name']])) ?>"
            loading="lazy"
            decoding="async"
        >
    </div>
</a>
