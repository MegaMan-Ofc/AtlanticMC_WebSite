<?php require_once dirname(__DIR__) . '/controllers/Site/maintenance.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<main class="maintenance-shell" id="main">
    <div class="maintenance-grid" aria-hidden="true"></div>
    <div class="maintenance-glow maintenance-glow--one" aria-hidden="true"></div>
    <div class="maintenance-glow maintenance-glow--two" aria-hidden="true"></div>

    <section class="maintenance-card" aria-labelledby="maintenance-title">
        <header class="maintenance-topbar">
            <a class="maintenance-brand" href="<?= e(route_url('home')) ?>" aria-label="Atlantic SMP">
                <img src="<?= e(url('assets/logo1.png')) ?>" alt="">
                <span>Atlantic SMP</span>
            </a>

            <form action="<?= e(url('actions/language.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="language" value="<?= e($maintenanceNextLanguage) ?>">
                <input type="hidden" name="return_to" value="<?= e(route_path('home')) ?>">
                <button class="maintenance-language" type="submit" aria-label="<?= e(t('language.switch_to_' . $maintenanceNextLanguage)) ?>">
                    <img src="<?= e(url($maintenanceLanguage === 'pt' ? 'assets/flag-pt.png' : 'assets/flag-en.png')) ?>" alt="" aria-hidden="true">
                    <span><?= e(language_label($maintenanceLanguage)) ?></span>
                </button>
            </form>
        </header>

        <div class="maintenance-content">
            <div class="maintenance-status" aria-label="<?= e(t('maintenance.status_label')) ?>">
                <span class="maintenance-status-pulse" aria-hidden="true"></span>
                <span><?= e(t('maintenance.status')) ?></span>
            </div>

            <div class="maintenance-visual" aria-hidden="true">
                <div class="maintenance-orbit maintenance-orbit--outer"></div>
                <div class="maintenance-orbit maintenance-orbit--inner"></div>
                <div class="maintenance-core">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <span class="maintenance-pixel maintenance-pixel--one"></span>
                <span class="maintenance-pixel maintenance-pixel--two"></span>
                <span class="maintenance-pixel maintenance-pixel--three"></span>
            </div>

            <div class="maintenance-copy">
                <span class="maintenance-kicker"><?= e(t('maintenance.kicker')) ?></span>
                <h1 id="maintenance-title"><?= e(t('maintenance.title')) ?></h1>
                <p><?= e(t('maintenance.description')) ?></p>
            </div>

            <?php if ((string) ($maintenanceState['message'] ?? '') !== ''): ?>
                <div class="maintenance-update">
                    <span><i class="fa-solid fa-bullhorn" aria-hidden="true"></i><?= e(t('maintenance.message_label')) ?></span>
                    <p><?= e((string) $maintenanceState['message']) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($maintenanceEndsTimestamp !== false && $maintenanceEndsTimestamp > time()): ?>
                <div class="maintenance-return">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <div>
                        <span><?= e(t('maintenance.estimated_return')) ?></span>
                        <strong><?= e(date('d/m/Y · H:i', $maintenanceEndsTimestamp)) ?></strong>
                    </div>
                </div>
            <?php endif; ?>

            <div class="maintenance-support">
                <p><?= e(t('maintenance.discord_help')) ?></p>
                <a class="button button--primary button--large" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-discord" aria-hidden="true"></i>
                    <?= e(t('maintenance.discord_action')) ?>
                </a>
            </div>
        </div>

        <footer class="maintenance-footer">
            <span><span class="maintenance-footer-dot" aria-hidden="true"></span><?= e(t('maintenance.http_status')) ?></span>
            <span><?= e(date('Y')) ?> · Atlantic SMP</span>
        </footer>
    </section>
</main>
</body>
</html>
