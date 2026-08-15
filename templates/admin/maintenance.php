<?php
$maintenanceEnabled = maintenance_is_enabled($adminMaintenanceState);
$maintenanceTargetEnabled = !$maintenanceEnabled;
$maintenanceCurrentValue = $maintenanceEnabled ? 'enabled' : 'disabled';
$maintenanceTargetValue = $maintenanceTargetEnabled ? 'enabled' : 'disabled';
$maintenanceConfirmationPhrase = maintenance_confirmation_phrase($maintenanceTargetEnabled);
$maintenanceUpdatedTimestamp = is_string($adminMaintenanceState['updated_at'] ?? null)
    ? strtotime((string) $adminMaintenanceState['updated_at'])
    : false;
$maintenanceEndsTimestamp = is_string($adminMaintenanceState['ends_at'] ?? null)
    ? strtotime((string) $adminMaintenanceState['ends_at'])
    : false;
$maintenanceIntegrityOk = ($adminMaintenanceState['integrity_ok'] ?? false) === true;
?>
<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.maintenance_title')) ?></h2>
        <p><?= e(t('admin.maintenance_text')) ?></p>
    </div>
</section>

<?php if (!$maintenanceIntegrityOk): ?>
    <div class="admin-inline-note admin-inline-note--warning admin-maintenance-integrity" role="alert">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <span><?= e(t('admin.maintenance_integrity_error')) ?></span>
    </div>
<?php endif; ?>

<section class="admin-maintenance-status <?= $maintenanceEnabled ? 'is-maintenance' : 'is-online' ?>">
    <div class="admin-maintenance-status-icon" aria-hidden="true">
        <span></span>
        <i class="fa-solid <?= $maintenanceEnabled ? 'fa-screwdriver-wrench' : 'fa-signal' ?>"></i>
    </div>

    <div class="admin-maintenance-status-copy">
        <span class="admin-maintenance-status-kicker"><?= e(t('admin.maintenance_online_kicker')) ?></span>
        <h3><?= e(t($maintenanceEnabled ? 'admin.maintenance_active_title' : 'admin.maintenance_online_title')) ?></h3>
        <p><?= e(t($maintenanceEnabled ? 'admin.maintenance_active_text' : 'admin.maintenance_online_text')) ?></p>
    </div>

    <div class="admin-maintenance-status-actions">
        <?php if ($maintenanceEnabled): ?>
            <a class="button button--ghost" href="<?= e(route_url('home')) ?>" target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <?= e(t('admin.maintenance_preview')) ?>
            </a>
        <?php endif; ?>
        <button
            class="button <?= $maintenanceEnabled ? 'button--primary' : 'button--danger' ?>"
            type="button"
            data-dialog-open="admin-maintenance-dialog"
        >
            <i class="fa-solid <?= $maintenanceEnabled ? 'fa-door-open' : 'fa-screwdriver-wrench' ?>" aria-hidden="true"></i>
            <?= e(t($maintenanceEnabled ? 'admin.maintenance_disable' : 'admin.maintenance_enable')) ?>
        </button>
    </div>
</section>

<div class="admin-maintenance-grid">
    <section class="admin-panel admin-maintenance-details">
        <div class="admin-maintenance-panel-heading">
            <span class="admin-maintenance-panel-icon"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span>
            <div>
                <h3><?= e(t('admin.maintenance_last_change')) ?></h3>
                <?php if ($maintenanceUpdatedTimestamp === false): ?>
                    <p><?= e(t('admin.maintenance_no_history')) ?></p>
                <?php else: ?>
                    <p><?= e(date('d/m/Y · H:i', $maintenanceUpdatedTimestamp)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <dl class="admin-maintenance-facts">
            <div>
                <dt><?= e(t('admin.maintenance_changed_by')) ?></dt>
                <dd><?= e((string) ($adminMaintenanceState['updated_by'] ?? '—')) ?></dd>
            </div>
            <div>
                <dt><?= e(t('admin.maintenance_public_message')) ?></dt>
                <dd><?= e((string) ($adminMaintenanceState['message'] ?? '') !== '' ? (string) $adminMaintenanceState['message'] : t('admin.maintenance_default_message')) ?></dd>
            </div>
            <div>
                <dt><?= e(t('admin.maintenance_estimated_return')) ?></dt>
                <dd><?= e($maintenanceEndsTimestamp === false ? t('admin.maintenance_no_estimate') : date('d/m/Y · H:i', $maintenanceEndsTimestamp)) ?></dd>
            </div>
        </dl>
    </section>

    <section class="admin-panel admin-maintenance-guards">
        <div class="admin-maintenance-panel-heading">
            <span class="admin-maintenance-panel-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
            <div>
                <h3><?= e(t('admin.maintenance_safeguards_title')) ?></h3>
                <p><?= e(t('admin.maintenance_safeguards_text')) ?></p>
            </div>
        </div>

        <div class="admin-maintenance-guard-list">
            <article>
                <i class="fa-solid fa-key" aria-hidden="true"></i>
                <div>
                    <strong><?= e(t('admin.maintenance_guard_password')) ?></strong>
                    <p><?= e(t('admin.maintenance_guard_password_text')) ?></p>
                </div>
            </article>
            <article>
                <i class="fa-solid fa-keyboard" aria-hidden="true"></i>
                <div>
                    <strong><?= e(t('admin.maintenance_guard_phrase')) ?></strong>
                    <p><?= e(t('admin.maintenance_guard_phrase_text')) ?></p>
                </div>
            </article>
            <article>
                <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                <div>
                    <strong><?= e(t('admin.maintenance_guard_state')) ?></strong>
                    <p><?= e(t('admin.maintenance_guard_state_text')) ?></p>
                </div>
            </article>
        </div>
    </section>
</div>

<dialog class="admin-dialog admin-maintenance-dialog" id="admin-maintenance-dialog" aria-labelledby="admin-maintenance-dialog-title">
    <form
        class="admin-dialog-form"
        action="<?= e(url('actions/admin_set_maintenance.php')) ?>"
        method="post"
        data-admin-maintenance-form
        data-confirmation-phrase="<?= e($maintenanceConfirmationPhrase) ?>"
    >
        <?= csrf_field() ?>
        <input type="hidden" name="current_state" value="<?= e($maintenanceCurrentValue) ?>">
        <input type="hidden" name="target_state" value="<?= e($maintenanceTargetValue) ?>">

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker"><?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_dialog_enable_kicker' : 'admin.maintenance_dialog_disable_kicker')) ?></span>
                <h3 id="admin-maintenance-dialog-title"><?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_dialog_enable_title' : 'admin.maintenance_dialog_disable_title')) ?></h3>
                <p><?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_dialog_enable_text' : 'admin.maintenance_dialog_disable_text')) ?></p>
            </div>
            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="<?= e(t('common.close')) ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="admin-dialog-body admin-maintenance-dialog-body">
            <ol class="admin-maintenance-stepper" aria-label="<?= e(t('admin.maintenance_title')) ?>">
                <li class="is-active" data-admin-maintenance-step-marker="1">
                    <span>1</span>
                    <strong><?= e(t('admin.maintenance_step_impact')) ?></strong>
                </li>
                <li data-admin-maintenance-step-marker="2">
                    <span>2</span>
                    <strong><?= e(t('admin.maintenance_step_phrase')) ?></strong>
                </li>
                <li data-admin-maintenance-step-marker="3">
                    <span>3</span>
                    <strong><?= e(t('admin.maintenance_step_password')) ?></strong>
                </li>
            </ol>

            <section class="admin-maintenance-step" data-admin-maintenance-step="1">
                <?php if ($maintenanceTargetEnabled): ?>
                    <div class="admin-maintenance-config-grid">
                        <label class="admin-field admin-maintenance-message-field">
                            <span><?= e(t('admin.maintenance_message_label')) ?></span>
                            <textarea
                                name="message"
                                maxlength="<?= MAINTENANCE_MESSAGE_MAX_LENGTH ?>"
                                rows="4"
                                placeholder="<?= e(t('admin.maintenance_message_placeholder')) ?>"
                            ><?= e((string) ($adminMaintenanceState['message'] ?? '')) ?></textarea>
                            <small><?= e(t('admin.maintenance_message_help')) ?></small>
                        </label>

                        <label class="admin-field">
                            <span><?= e(t('admin.maintenance_end_label')) ?></span>
                            <input
                                class="field"
                                type="datetime-local"
                                name="ends_at"
                                min="<?= e(date('Y-m-d\TH:i', time() + 300)) ?>"
                                value="<?= $maintenanceEndsTimestamp === false ? '' : e(date('Y-m-d\TH:i', $maintenanceEndsTimestamp)) ?>"
                            >
                            <small><?= e(t('admin.maintenance_end_help')) ?></small>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="admin-maintenance-acknowledgements">
                    <label>
                        <input type="checkbox" name="acknowledge_impact" value="1" data-admin-maintenance-required>
                        <span><?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_ack_impact_enable' : 'admin.maintenance_ack_impact_disable')) ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="acknowledge_access" value="1" data-admin-maintenance-required>
                        <span><?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_ack_access_enable' : 'admin.maintenance_ack_access_disable')) ?></span>
                    </label>
                </div>
            </section>

            <section class="admin-maintenance-step" data-admin-maintenance-step="2" hidden>
                <div class="admin-maintenance-confirmation-copy">
                    <i class="fa-solid fa-keyboard" aria-hidden="true"></i>
                    <p><?= e(t('admin.maintenance_phrase_instruction')) ?></p>
                    <code><?= e($maintenanceConfirmationPhrase) ?></code>
                </div>

                <label class="admin-field">
                    <span><?= e(t('admin.maintenance_phrase_label')) ?></span>
                    <input
                        class="field"
                        type="text"
                        name="confirmation_phrase"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        data-admin-maintenance-phrase
                    >
                </label>
            </section>

            <section class="admin-maintenance-step" data-admin-maintenance-step="3" hidden>
                <div class="admin-maintenance-password-copy">
                    <span class="admin-maintenance-lock"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                    <div>
                        <h4><?= e(t('admin.maintenance_password_label')) ?></h4>
                        <p><?= e(t('admin.maintenance_password_instruction')) ?></p>
                    </div>
                </div>

                <label class="admin-field">
                    <span><?= e(t('admin.maintenance_password_label')) ?></span>
                    <div class="admin-password-control">
                        <input
                            id="admin-maintenance-password"
                            class="field"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            data-admin-maintenance-password
                        >
                        <button class="admin-password-toggle" type="button" data-password-toggle data-show-label="<?= e(t('admin.show_password')) ?>" data-hide-label="<?= e(t('admin.hide_password')) ?>" aria-controls="admin-maintenance-password" aria-pressed="false" aria-label="<?= e(t('admin.show_password')) ?>" title="<?= e(t('admin.show_password')) ?>">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <small><?= e(t('admin.maintenance_password_help')) ?></small>
                </label>
            </section>
        </div>

        <footer class="admin-dialog-actions admin-maintenance-dialog-actions">
            <button class="button button--ghost" type="button" data-admin-maintenance-back hidden>
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <?= e(t('admin.maintenance_back')) ?>
            </button>
            <span class="admin-dialog-actions-spacer"></span>
            <button class="button button--ghost" type="button" data-dialog-close><?= e(t('common.close')) ?></button>
            <button class="button button--primary" type="button" data-admin-maintenance-next disabled>
                <?= e(t('admin.maintenance_continue')) ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
            <button
                class="button <?= $maintenanceTargetEnabled ? 'button--danger' : 'button--primary' ?>"
                type="submit"
                data-admin-maintenance-submit
                hidden
                disabled
            >
                <i class="fa-solid <?= $maintenanceTargetEnabled ? 'fa-screwdriver-wrench' : 'fa-door-open' ?>" aria-hidden="true"></i>
                <?= e(t($maintenanceTargetEnabled ? 'admin.maintenance_enable_now' : 'admin.maintenance_disable_now')) ?>
            </button>
        </footer>
    </form>
</dialog>
