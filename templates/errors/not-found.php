<section class="not-found" aria-labelledby="not-found-title">
    <div class="not-found-visual" aria-hidden="true">
        <div class="not-found-code">404</div>
        <div class="not-found-cube">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <img src="<?= e(url('assets/logo1.png')) ?>" alt="">
        </div>
    </div>

    <div class="not-found-copy">
        <span class="not-found-kicker"><?= e(t('not_found.kicker')) ?></span>
        <h1 id="not-found-title"><?= e(t('not_found.title')) ?></h1>
        <p><?= e(t('not_found.description')) ?></p>

        <div class="not-found-actions">
            <a class="button button--primary button--large" href="<?= e(route_url('home')) ?>">
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <?= e(t('not_found.home_action')) ?>
            </a>
            <a class="button button--ghost button--large" href="<?= e(route_url('search')) ?>">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <?= e(t('not_found.search_action')) ?>
            </a>
        </div>

        <p class="not-found-help">
            <?= e(t('not_found.help')) ?>
            <a href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('not_found.discord_action')) ?></a>
        </p>
    </div>
</section>
