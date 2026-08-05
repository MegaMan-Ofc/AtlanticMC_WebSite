<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.coupons')) ?></h2>
        <p><?= e(t('admin.coupons_text')) ?></p>
    </div>
</section>

<details class="admin-panel admin-create-panel">
    <summary><?= e(t('admin.create_coupon')) ?></summary>
    <form class="admin-form" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="0">
        <div class="admin-form-grid">
            <div class="admin-field"><label><?= e(t('common.code')) ?></label><input name="code" maxlength="50" required></div>
            <div class="admin-field"><label><?= e(t('common.type')) ?></label><select name="discount_type"><option value="percentage"><?= e(t('admin.percentage')) ?></option><option value="fixed"><?= e(t('admin.fixed_eur')) ?></option></select></div>
            <div class="admin-field"><label><?= e(t('common.value')) ?></label><input name="discount_value" inputmode="decimal" required></div>
            <div class="admin-field"><label><?= e(t('admin.minimum_subtotal')) ?></label><input name="min_subtotal" value="0" inputmode="decimal"></div>
            <div class="admin-field"><label><?= e(t('admin.maximum_uses')) ?></label><input name="max_uses" type="number" min="1"></div>
            <div class="admin-field"><label><?= e(t('admin.expires_at')) ?></label><input name="expires_at" type="datetime-local"></div>
            <label class="admin-check"><input name="active" type="checkbox" value="1" checked><span><?= e(t('common.active')) ?></span></label>
        </div>
        <button class="button button--primary" type="submit"><?= e(t('admin.create_coupon')) ?></button>
    </form>
</details>

<div class="admin-coupon-list">
    <?php foreach ($adminCoupons as $coupon): ?>
        <form class="admin-panel admin-coupon-card" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $coupon['id'] ?>">
            <div class="admin-coupon-heading">
                <strong><?= e($coupon['code']) ?></strong>
                <span><?= e(t('admin.used_count', ['count' => (int) $coupon['used_count']])) ?></span>
            </div>
            <div class="admin-form-grid">
                <div class="admin-field"><label><?= e(t('common.code')) ?></label><input name="code" value="<?= e($coupon['code']) ?>" maxlength="50" required></div>
                <div class="admin-field"><label><?= e(t('common.type')) ?></label><select name="discount_type"><option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>><?= e(t('admin.percentage')) ?></option><option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>><?= e(t('admin.fixed_eur')) ?></option></select></div>
                <div class="admin-field"><label><?= e(t('common.value')) ?></label><input name="discount_value" value="<?= e($coupon['discount_type'] === 'fixed' ? number_format((int) $coupon['discount_value'] / 100, 2, '.', '') : $coupon['discount_value']) ?>"></div>
                <div class="admin-field"><label><?= e(t('admin.minimum_subtotal')) ?></label><input name="min_subtotal" value="<?= e(number_format((int) $coupon['min_subtotal_cents'] / 100, 2, '.', '')) ?>"></div>
                <div class="admin-field"><label><?= e(t('admin.maximum_uses')) ?></label><input name="max_uses" value="<?= e($coupon['max_uses'] ?? '') ?>" type="number" min="1"></div>
                <div class="admin-field"><label><?= e(t('admin.expires_at')) ?></label><input name="expires_at" value="<?= e($coupon['expires_at'] ? date('Y-m-d\\TH:i', strtotime($coupon['expires_at'])) : '') ?>" type="datetime-local"></div>
                <label class="admin-check"><input name="active" type="checkbox" value="1" <?= (bool) $coupon['active'] ? 'checked' : '' ?>><span><?= e(t('common.active')) ?></span></label>
            </div>
            <div class="admin-card-actions">
                <button class="button button--primary" type="submit"><?= e(t('common.save')) ?></button>
                <button class="button admin-danger-button" type="submit" formaction="<?= e(url('actions/admin_delete_coupon.php')) ?>"><?= e(t('common.delete')) ?></button>
            </div>
        </form>
    <?php endforeach; ?>
</div>
