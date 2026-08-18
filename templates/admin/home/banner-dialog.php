<dialog
    class="admin-dialog admin-home-banner-dialog"
    id="admin-home-banner-dialog"
    aria-labelledby="admin-home-banner-dialog-title"
    data-default-kicker="<?= e(t('home.category_banner_kicker')) ?>"
    data-default-cta="<?= e(t('home.category_banner_action')) ?>"
>
    <form
        class="admin-dialog-form"
        action="<?= e(url('actions/admin_save_home_banner.php')) ?>"
        method="post"
        data-admin-home-banner-form
    >
        <?= csrf_field() ?>
        <input type="hidden" name="category_id" value="0" data-admin-home-banner-category-id>

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker"><?= e(t('admin.home_banner_dialog_kicker')) ?></span>
                <h3 id="admin-home-banner-dialog-title"><?= e(t('admin.home_banner_dialog_title')) ?></h3>
                <p data-admin-home-banner-dialog-category></p>
            </div>
            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="<?= e(t('common.close')) ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="admin-dialog-body admin-home-banner-dialog-body">
            <section class="admin-home-banner-preview-wrap">
                <div
                    class="admin-home-banner-preview"
                    data-admin-home-banner-preview
                    data-style="auto"
                    data-image-side="right"
                    data-image-size="normal"
                >
                    <span class="admin-home-banner-preview-watermark" data-admin-home-banner-preview-watermark></span>
                    <div class="admin-home-banner-preview-copy">
                        <small data-admin-home-banner-preview-kicker></small>
                        <strong data-admin-home-banner-preview-title></strong>
                        <p data-admin-home-banner-preview-text hidden></p>
                        <span data-admin-home-banner-preview-cta>
                            <span data-admin-home-banner-preview-cta-label></span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="admin-home-banner-preview-image">
                        <img src="" alt="" data-admin-home-banner-preview-image>
                    </div>
                </div>
                <p><?= e(t('admin.home_banner_preview_help')) ?></p>
            </section>

            <div class="admin-home-banner-form-grid">
                <section class="admin-home-banner-form-section">
                    <div class="admin-home-banner-form-heading">
                        <i class="fa-solid fa-font" aria-hidden="true"></i>
                        <div>
                            <strong><?= e(t('admin.home_banner_content')) ?></strong>
                            <small><?= e(t('admin.home_banner_content_help')) ?></small>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-kicker"><?= e(t('admin.home_banner_kicker_label')) ?></label>
                        <input id="admin-home-banner-kicker" name="kicker" type="text" maxlength="80" data-admin-home-banner-input="kicker">
                        <small><?= e(t('admin.home_banner_kicker_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-title"><?= e(t('admin.home_banner_title_label')) ?></label>
                        <input id="admin-home-banner-title" name="title" type="text" maxlength="120" data-admin-home-banner-input="title">
                        <small><?= e(t('admin.home_banner_title_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-text"><?= e(t('admin.home_banner_text_label')) ?></label>
                        <textarea id="admin-home-banner-text" name="text" rows="3" maxlength="255" data-admin-home-banner-input="text"></textarea>
                        <small><?= e(t('admin.home_banner_text_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-cta"><?= e(t('admin.home_banner_cta_label')) ?></label>
                        <input id="admin-home-banner-cta" name="cta" type="text" maxlength="80" data-admin-home-banner-input="cta">
                        <small><?= e(t('admin.home_banner_cta_help')) ?></small>
                    </div>
                </section>

                <section class="admin-home-banner-form-section">
                    <div class="admin-home-banner-form-heading">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                        <div>
                            <strong><?= e(t('admin.home_banner_appearance')) ?></strong>
                            <small><?= e(t('admin.home_banner_appearance_help')) ?></small>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-style"><?= e(t('admin.home_banner_style_label')) ?></label>
                        <select id="admin-home-banner-style" name="style" data-admin-home-banner-input="style">
                            <?php foreach (home_banner_style_options() as $style): ?>
                                <option value="<?= e($style) ?>"><?= e(t('admin.home_banner_style_' . $style)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-home-banner-inline-fields">
                        <div class="admin-field">
                            <label for="admin-home-banner-image-side"><?= e(t('admin.home_banner_image_side_label')) ?></label>
                            <select id="admin-home-banner-image-side" name="image_side" data-admin-home-banner-input="image_side">
                                <option value="right"><?= e(t('admin.home_banner_image_side_right')) ?></option>
                                <option value="left"><?= e(t('admin.home_banner_image_side_left')) ?></option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="admin-home-banner-image-size"><?= e(t('admin.home_banner_image_size_label')) ?></label>
                            <select id="admin-home-banner-image-size" name="image_size" data-admin-home-banner-input="image_size">
                                <option value="compact"><?= e(t('admin.home_banner_image_size_compact')) ?></option>
                                <option value="normal"><?= e(t('admin.home_banner_image_size_normal')) ?></option>
                                <option value="large"><?= e(t('admin.home_banner_image_size_large')) ?></option>
                            </select>
                        </div>
                    </div>

                    <label class="admin-home-banner-switch">
                        <input type="checkbox" name="show_watermark" value="1" data-admin-home-banner-input="show_watermark">
                        <span>
                            <strong><?= e(t('admin.home_banner_watermark_label')) ?></strong>
                            <small><?= e(t('admin.home_banner_watermark_help')) ?></small>
                        </span>
                    </label>

                    <label class="admin-home-banner-switch">
                        <input type="checkbox" name="show_cta" value="1" data-admin-home-banner-input="show_cta">
                        <span>
                            <strong><?= e(t('admin.home_banner_show_cta_label')) ?></strong>
                            <small><?= e(t('admin.home_banner_show_cta_help')) ?></small>
                        </span>
                    </label>
                </section>
            </div>

            <div class="admin-inline-note admin-inline-note--info admin-home-banner-note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_note')) ?></span>
            </div>
        </div>

        <footer class="admin-dialog-actions">
            <button class="button button--ghost" type="button" data-admin-home-banner-reset>
                <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                <?= e(t('admin.home_banner_reset')) ?>
            </button>
            <span class="admin-home-banner-form-state" data-admin-home-banner-form-state aria-live="polite"></span>
            <span class="admin-dialog-actions-spacer"></span>
            <button class="button button--ghost" type="button" data-dialog-close><?= e(t('common.close')) ?></button>
            <button class="button button--primary" type="submit" data-admin-home-banner-save disabled>
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                <?= e(t('common.save')) ?>
            </button>
        </footer>
    </form>
</dialog>
