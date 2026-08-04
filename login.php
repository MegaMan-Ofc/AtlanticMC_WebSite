<?php require_once __DIR__ . '/controllers/login.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content login-main" id="main">
        <section class="login-card">
            <a class="login-back" href="<?= e(url('index.php')) ?>" aria-label="Back to store"><i class="fa-solid fa-arrow-left"></i></a>
            <?php if ($loginUser !== null): ?>
                <img class="login-avatar" src="<?= e($loginUser['avatar_url']) ?>" alt="<?= e($loginUser['minecraft_name']) ?>">
                <h1>Logged in as <?= e($loginUser['minecraft_name']) ?></h1>
                <p>Your Minecraft identity is stored in the server session.</p>
                <form action="<?= e(url('actions/logout.php')) ?>" method="post" class="login-form">
                    <?= csrf_field() ?>
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <i class="fa-brands fa-microsoft login-provider-icon" aria-hidden="true"></i>
                <h1>Minecraft Login</h1>
                <p>Use the Microsoft account that owns Minecraft. The site never receives your Microsoft password.</p>
                <?php if ($loginConfigured): ?>
                    <form action="<?= e(url('actions/login.php')) ?>" method="post" class="login-form">
                        <?= csrf_field() ?>
                        <button type="submit"><i class="fa-brands fa-microsoft"></i> Continue with Microsoft</button>
                    </form>
                <?php else: ?>
                    <div class="login-config-warning">
                        <strong>Login configuration required</strong>
                        <p>Set MINECRAFT_CLIENT_ID, MINECRAFT_CLIENT_SECRET and MINECRAFT_REDIRECT_URI in the private .env file.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
