<?php
$couponId = (int) $couponForm['id'];
$couponDialogId = $couponId > 0 ? 'admin-coupon-dialog-' . $couponId : 'admin-coupon-dialog-new';
$couponTitleId = $couponDialogId . '-title';
$couponIsNew = $couponId === 0;
?>
<dialog class="admin-dialog" id="<?= e($couponDialogId) ?>" aria-labelledby="<?= e($couponTitleId) ?>">
    <form class="admin-dialog-form" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post" <?php if (!$couponIsNew): ?>data-confirm-delete="<?= e(t('admin.delete_coupon_confirm')) ?>"<?php endif; ?>>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $couponId ?>">
        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker"><?= e(t('admin.section_coupons')) ?></span>
                <h3 id="<?= e($couponTitleId) ?>"><?= e($couponIsNew ? t('admin.create_coupon') : (string) $couponForm['code']) ?></h3>
                <?php if (!$couponIsNew): ?><p><?= e(t('admin.used_count', ['count' => (int) $couponForm['used_count']])) ?></p><?php endif; ?>
            </div>
            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="<?= e(t('common.close')) ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="admin-dialog-body">
            <div class="admin-form-grid">
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-code"><?= e(t('common.code')) ?></label><input id="<?= e($couponDialogId) ?>-code" name="code" value="<?= e((string) $couponForm['code']) ?>" maxlength="50" required></div>
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-type"><?= e(t('common.type')) ?></label><select id="<?= e($couponDialogId) ?>-type" name="discount_type"><option value="percentage" <?= $couponForm['discount_type'] === 'percentage' ? 'selected' : '' ?>><?= e(t('admin.percentage')) ?></option><option value="fixed" <?= $couponForm['discount_type'] === 'fixed' ? 'selected' : '' ?>><?= e(t('admin.fixed_eur')) ?></option></select></div>
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-value"><?= e(t('common.value')) ?></label><input id="<?= e($couponDialogId) ?>-value" name="discount_value" value="<?= e($couponForm['discount_type'] === 'fixed' ? number_format((int) $couponForm['discount_value'] / 100, 2, '.', '') : (string) $couponForm['discount_value']) ?>" inputmode="decimal" required></div>
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-minimum"><?= e(t('admin.minimum_subtotal')) ?></label><input id="<?= e($couponDialogId) ?>-minimum" name="min_subtotal" value="<?= e(number_format((int) $couponForm['min_subtotal_cents'] / 100, 2, '.', '')) ?>" inputmode="decimal"></div>
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-maximum"><?= e(t('admin.maximum_uses')) ?></label><input id="<?= e($couponDialogId) ?>-maximum" name="max_uses" value="<?= e((string) ($couponForm['max_uses'] ?? '')) ?>" type="number" min="1"></div>
                <div class="admin-field"><label for="<?= e($couponDialogId) ?>-expiry"><?= e(t('admin.expires_at')) ?></label><input id="<?= e($couponDialogId) ?>-expiry" name="expires_at" value="<?= e($couponForm['expires_at'] ? date('Y-m-d\\TH:i', strtotime((string) $couponForm['expires_at'])) : '') ?>" type="datetime-local"></div>
                <label class="admin-check"><input name="active" type="checkbox" value="1" <?= (bool) $couponForm['active'] ? 'checked' : '' ?>><span><?= e(t('common.active')) ?></span></label>
            </div>
        </div>
        <footer class="admin-dialog-actions">
            <?php if (!$couponIsNew): ?><button class="button admin-danger-button" type="submit" formaction="<?= e(url('actions/admin_delete_coupon.php')) ?>" data-delete-coupon><?= e(t('common.delete')) ?></button><?php endif; ?>
            <span class="admin-dialog-actions-spacer"></span>
            <button class="button button--ghost" type="button" data-dialog-close><?= e(t('common.close')) ?></button>
            <button class="button button--primary" type="submit"><?= e($couponIsNew ? t('admin.create_coupon') : t('common.save')) ?></button>
        </footer>
    </form>
</dialog>
