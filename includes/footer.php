<footer class="site-footer">
    <div class="footer-main">
        <div class="container footer-grid">
            <section class="footer-about">
                <h2>About us</h2>
                <p>Atlantic Anarchy is a public Minecraft anarchy server for Java and Bedrock players. Join us at <strong><?= e(config('app.server_ip')) ?></strong>.</p>
            </section>
            <nav aria-label="Store links">
                <h2>Store</h2>
                <div class="footer-links">
                    <a href="<?= e(url('index.php')) ?>">Home</a>
                    <a href="<?= e(url('ranks.php')) ?>">VIPs</a>
                    <a href="<?= e(url('rubis.php')) ?>">Rubis</a>
                    <a href="<?= e(url('keys.php')) ?>">Keys</a>
                </div>
            </nav>
            <section>
                <h2>Support</h2>
                <p>Questions before purchasing? Contact us through Discord or email.</p>
                <div class="footer-actions">
                    <a class="button button--primary" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer">Discord</a>
                    <a class="button button--ghost" href="mailto:<?= e(config('app.support_email')) ?>">Email</a>
                </div>
            </section>
            <nav class="footer-legal-links" aria-label="Legal information">
                <h2>Legal</h2>
                <div class="footer-links">
                    <span class="footer-link--disabled">Privacy Policy</span>
                    <span class="footer-link--disabled">Terms of Service</span>
                    <a href="https://www.livroreclamacoes.pt/Inicio/" target="_blank" rel="noopener noreferrer">Book of Complaints</a>
                </div>
            </nav>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container footer-row">
            <div class="footer-legal">
                <p><?= date('Y') ?> © <strong>Atlantic Anarchy</strong></p>
                <p>We are not affiliated with or endorsed by Mojang, AB.</p>
            </div>
            <ul class="footer-socials" aria-label="Social links">
                <li><a href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer" aria-label="Discord"><i class="fa-brands fa-discord" aria-hidden="true"></i></a></li>
                <li><a href="#top" aria-label="Back to top"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a></li>
            </ul>
        </div>
    </div>
</footer>
<script defer src="<?= e(url('js/main.js')) ?>"></script>
