<?php require_once dirname(__DIR__) . '/controllers/Site/faq.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <header class="page-title faq-page-title">
                <a aria-label="<?= e(t('common.back')) ?>" href="<?= e(route_url('home')) ?>">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                </a>
                <div>
                    <h1><?= e(t('faq.title')) ?></h1>
                    <p class="page-subtitle"><?= e(t('faq.description')) ?></p>
                </div>
            </header>

            <nav class="legal-navigation" aria-label="<?= e(t('legal.navigation_aria')) ?>">
                <?php foreach ($legalNavigation as $item): ?>
                    <a href="<?= e($item['url']) ?>">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= e(route_url('faq')) ?>" aria-current="page">
                    <?= e(t('common.faq')) ?>
                </a>
                <a href="https://www.livroreclamacoes.pt/Inicio/" target="_blank" rel="noopener noreferrer">
                    <?= e(t('footer.complaints')) ?>
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
            </nav>

            <section class="faq-list" aria-label="<?= e(t('faq.list_aria')) ?>">
                <?php foreach ($faqItems as $item): ?>
                    <details class="faq-item">
                        <summary>
                            <span><?= e((string) $item['question']) ?></span>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="faq-answer">
                            <p><?= e((string) $item['answer']) ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
