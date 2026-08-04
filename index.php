<?php require_once __DIR__ . '/controllers/index.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <h1 class="sr-only">Atlantic Anarchy Store</h1>
            <a class="home-hero" href="<?= e(url('keys.php')) ?>">
                <img alt="Magma crate key" src="<?= e(url('assets/magma-key.png')) ?>">
                <div><strong>Order now →</strong><small>SERVER-SIDE CART ENABLED</small></div>
            </a>
            <section aria-label="Store categories" class="category-grid">
                <a class="category-card" data-theme="vips" href="<?= e(url('ranks.php')) ?>"><img alt="Diamond" src="<?= e(url('assets/diamante.png')) ?>"><h2>VIPs</h2></a>
                <a class="category-card" data-theme="rubis" href="<?= e(url('rubis.php')) ?>"><img alt="Bag of Rubis" src="<?= e(url('assets/rubis-saco-pequeno.png.png')) ?>"><h2>Rubis</h2></a>
                <a class="category-card" data-theme="keys" href="<?= e(url('keys.php')) ?>"><img alt="Atlantic key" src="<?= e(url('assets/atlantic-key.png')) ?>"><h2>Keys</h2></a>
                <a class="category-card" data-theme="money" href="<?= e(url('battlepass.php')) ?>"><i aria-hidden="true" class="fa-solid fa-ticket"></i><h2>Battle Pass</h2><small>Season rewards</small></a>
            </section>
            <a class="promo-card sdz-rank-card" href="<?= e(url('boosters.php')) ?>">
                <div><p>Power up your game</p><h2>Hearts<br><small>Extra life</small></h2><span class="button button--ghost">View hearts →</span></div>
                <img alt="Extra life heart" src="<?= e(url('assets/heart%20(2).png')) ?>">
            </a>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
