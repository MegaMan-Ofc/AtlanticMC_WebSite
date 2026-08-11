<footer class="site-footer">
    <div class="footer-main">
        <div class="container footer-grid">
            <section class="footer-about">
                <h2><?= e(t('footer.about_title')) ?></h2>
                <p><?= e(t('footer.about_text', ['server' => (string) config('app.server_ip')])) ?></p>
            </section>
            <nav aria-label="<?= e(t('footer.store_links_aria')) ?>">
                <h2><?= e(t('common.store')) ?></h2>
                <div class="footer-links">
                    <a href="<?= e(route_url('home')) ?>"><?= e(t('common.home')) ?></a>
                    <a href="<?= e(route_url('ranks')) ?>"><?= e(localized_category('ranks')) ?></a>
                    <a href="<?= e(route_url('rubis')) ?>"><?= e(localized_category('rubis')) ?></a>
                    <a href="<?= e(route_url('keys')) ?>"><?= e(localized_category('keys')) ?></a>
                </div>
            </nav>
            <section>
                <h2><?= e(t('common.support')) ?></h2>
                <p><?= e(t('footer.questions')) ?></p>
                <div class="footer-actions">
                    <a class="button button--primary" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer">Discord</a>
                    <a class="button button--ghost" href="mailto:<?= e(config('app.support_email')) ?>"><?= e(t('common.email')) ?></a>
                </div>
            </section>
            <nav class="footer-legal-links" aria-label="<?= e(t('footer.legal_aria')) ?>">
                <h2><?= e(t('common.legal')) ?></h2>
                <div class="footer-links">
                    <a href="<?= e(route_url('privacy')) ?>"><?= e(t('footer.privacy')) ?></a>
                    <a href="<?= e(route_url('terms')) ?>"><?= e(t('footer.terms')) ?></a>
                    <a href="<?= e(route_url('purchase-policy')) ?>"><?= e(t('footer.purchase_policy')) ?></a>
                    <a href="<?= e(route_url('rules')) ?>"><?= e(t('footer.rules')) ?></a>
                    <a href="https://www.livroreclamacoes.pt/Inicio/" target="_blank" rel="noopener noreferrer"><?= e(t('footer.complaints')) ?></a>
                </div>
            </nav>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container footer-row">
            <div class="footer-legal">
                <p><?= date('Y') ?> © <strong>Atlantic Anarchy</strong></p>
                <p><?= e(t('footer.disclaimer')) ?></p>
            </div>
            <ul class="footer-socials" aria-label="<?= e(t('footer.social_aria')) ?>">
                <li><a href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer" aria-label="Discord"><i class="fa-brands fa-discord" aria-hidden="true"></i></a></li>
                <li><a href="#top" aria-label="<?= e(t('footer.back_to_top')) ?>"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a></li>
            </ul>
        </div>
    </div>
</footer>
<div
    class="site-notice"
    data-site-notice
    data-message-invalid-response="<?= e(t('js.invalid_response')) ?>"
    data-message-request-failed="<?= e(t('js.request_failed')) ?>"
    data-message-cart-updated="<?= e(t('js.cart_updated')) ?>"
    data-message-cart-failed="<?= e(t('js.cart_failed')) ?>"
    data-message-checkout-url-missing="<?= e(t('js.checkout_url_missing')) ?>"
    data-message-checkout-failed="<?= e(t('js.checkout_failed')) ?>"
    data-message-copy-failed="<?= e(t('js.copy_failed')) ?>"
    data-message-copied="<?= e(t('js.copied')) ?>"
    data-message-language-failed="<?= e(t('js.language_failed')) ?>"
    role="status"
    aria-live="polite"
    hidden
></div>
<script defer src="<?= e(url('js/header.js')) ?>"></script>
<script defer src="<?= e(url('js/main.js')) ?>"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
    <script defer src="<?= e(url($script)) ?>"></script>
<?php endforeach; ?>
