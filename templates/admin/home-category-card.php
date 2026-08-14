<?php
$adminHomeCategory ??= null;

if (!is_array($adminHomeCategory)) {
    return;
}
?>
<article
    class="admin-home-category-card<?= !(bool) $adminHomeCategory['active'] ? ' is-inactive' : '' ?>"
    data-admin-home-category
    data-category-id="<?= (int) $adminHomeCategory['id'] ?>"
    draggable="true"
>
    <div class="admin-home-category-card-image">
        <img src="<?= e(url((string) $adminHomeCategory['image'])) ?>" alt="" loading="lazy">
    </div>
    <div class="admin-home-category-card-copy">
        <small><?= e(t('admin.home_category_label')) ?></small>
        <strong><?= e((string) $adminHomeCategory['name']) ?></strong>
        <?php if (!(bool) $adminHomeCategory['active']): ?>
            <span class="admin-home-category-inactive"><?= e(t('admin.home_category_inactive')) ?></span>
        <?php endif; ?>
    </div>
    <span class="admin-home-category-drag" title="<?= e(t('admin.home_category_drag')) ?>">
        <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
    </span>
</article>
