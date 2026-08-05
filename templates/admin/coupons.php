<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.coupons')) ?></h2>
        <p><?= e(t('admin.coupons_text')) ?></p>
    </div>
</section>

<?php
$newCoupon = [
    'id' => 0,
    'code' => '',
    'discount_type' => 'percentage',
    'discount_value' => 10,
    'min_subtotal_cents' => 0,
    'max_uses' => null,
    'used_count' => 0,
    'active' => 1,
    'expires_at' => null,
];
?>

<div class="admin-entity-grid admin-coupon-grid" aria-label="<?= e(t('admin.coupons')) ?>">
    <button class="admin-entity-card admin-entity-card--create" type="button" data-dialog-open="admin-coupon-dialog-new" aria-haspopup="dialog" aria-controls="admin-coupon-dialog-new">
        <span class="admin-entity-icon"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
        <strong><?= e(t('admin.create_coupon')) ?></strong>
    </button>

    <?php foreach ($adminCoupons as $coupon): ?>
        <button class="admin-entity-card admin-coupon-tile <?= (bool) $coupon['active'] ? '' : 'is-inactive' ?>" type="button" data-dialog-open="admin-coupon-dialog-<?= (int) $coupon['id'] ?>" aria-haspopup="dialog" aria-controls="admin-coupon-dialog-<?= (int) $coupon['id'] ?>" aria-label="<?= e(t('admin.edit_coupon', ['code' => (string) $coupon['code']])) ?>">
            <strong><?= e((string) $coupon['code']) ?></strong>
            <span><?= e(format_admin_coupon_discount($coupon)) ?></span>
        </button>
    <?php endforeach; ?>
</div>

<?php $couponForm = $newCoupon; require BASE_PATH . '/templates/admin/coupon-dialog.php'; ?>
<?php foreach ($adminCoupons as $couponForm): ?>
    <?php require BASE_PATH . '/templates/admin/coupon-dialog.php'; ?>
<?php endforeach; ?>
