<?php require_once dirname(__DIR__) . '/controllers/faq.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/includes/header.php'; ?>
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
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
