<?php
$legalSections = is_array($legalPage['sections'] ?? null) ? $legalPage['sections'] : [];
$legalNavigation = is_array($legalPage['navigation'] ?? null) ? $legalPage['navigation'] : [];
?>
<header class="page-title legal-page-title">
    <a aria-label="<?= e(t('common.back')) ?>" href="<?= e(url('index.php')) ?>">
        <i class="fa-solid fa-house" aria-hidden="true"></i>
    </a>
    <div>
        <h1><?= e($legalPage['title']) ?></h1>
        <p class="page-subtitle"><?= e($legalPage['description']) ?></p>
    </div>
</header>

<nav class="legal-navigation" aria-label="<?= e(t('legal.navigation_aria')) ?>">
    <?php foreach ($legalNavigation as $item): ?>
        <a
            href="<?= e($item['url']) ?>"
            <?= $item['slug'] === $legalPage['slug'] ? 'aria-current="page"' : '' ?>
        >
            <?= e($item['label']) ?>
        </a>
    <?php endforeach; ?>
    <a href="https://www.livroreclamacoes.pt/Inicio/" target="_blank" rel="noopener noreferrer">
        <?= e(t('footer.complaints')) ?>
        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
    </a>
</nav>

<article class="legal-document">
    <header class="legal-document__header">
        <div class="legal-document__icon" aria-hidden="true">
            <i class="fa-solid <?= e($legalPage['icon']) ?>"></i>
        </div>
        <div>
            <p><?= e($legalPage['intro']) ?></p>
            <small><?= e(t('legal.last_updated', ['date' => $legalPage['lastUpdated']])) ?></small>
        </div>
    </header>

    <?php if ((bool) config('app.debug', false) && ($legalPage['needsReview'] ?? false)): ?>
        <aside class="legal-development-notice" role="note">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            <div>
                <strong><?= e(t('legal.setup_warning_title')) ?></strong>
                <p><?= e(t('legal.setup_warning_text')) ?></p>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($legalSections !== []): ?>
        <nav class="legal-contents" aria-label="<?= e(t('legal.contents_aria')) ?>">
            <strong><?= e(t('legal.contents_title')) ?></strong>
            <ol>
                <?php foreach ($legalSections as $index => $section): ?>
                    <li>
                        <a href="#section-<?= $index + 1 ?>"><?= e((string) ($section['title'] ?? '')) ?></a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>

    <div class="legal-sections">
        <?php foreach ($legalSections as $index => $section): ?>
            <section id="section-<?= $index + 1 ?>" class="legal-section">
                <h2><?= e((string) ($section['title'] ?? '')) ?></h2>

                <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                    <p><?= e((string) $paragraph) ?></p>
                <?php endforeach; ?>

                <?php if (($section['items'] ?? []) !== []): ?>
                    <ul>
                        <?php foreach ($section['items'] as $item): ?>
                            <li><?= e((string) $item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (isset($section['note']) && is_string($section['note'])): ?>
                    <p class="legal-section__note">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <?= e($section['note']) ?>
                    </p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <section class="legal-operator" aria-labelledby="legal-operator-title">
        <div>
            <h2 id="legal-operator-title"><?= e(t('legal.operator_title')) ?></h2>
            <p><?= e(t('legal.operator_intro')) ?></p>
        </div>
        <dl>
            <?php foreach ($legalPage['operator'] as $detail): ?>
                <div>
                    <dt><?= e($detail['label']) ?></dt>
                    <dd>
                        <?php if (($detail['email'] ?? false) === true): ?>
                            <a href="mailto:<?= e($detail['value']) ?>"><?= e($detail['value']) ?></a>
                        <?php else: ?>
                            <?= e($detail['value']) ?>
                        <?php endif; ?>
                    </dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <footer class="legal-document__footer">
        <p><?= e(t('legal.mandatory_rights')) ?></p>
        <div>
            <a class="button button--primary" href="mailto:<?= e(config('legal.privacy_email', config('app.support_email'))) ?>">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <?= e(t('legal.contact_us')) ?>
            </a>
            <a class="button button--ghost" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer">
                <i class="fa-brands fa-discord" aria-hidden="true"></i>
                Discord
            </a>
        </div>
    </footer>
</article>
