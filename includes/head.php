<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b0e13">
    <meta name="description" content="<?= e($pageDescription ?? 'Atlantic Anarchy Minecraft Store') ?>">
    <title><?= e($pageTitle ?? config('app.name')) ?></title>
    <link href="<?= e(url('assets/logo1.png')) ?>" rel="icon" type="image/png">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= e(url('css/base.css')) ?>" rel="stylesheet">
    <link href="<?= e(url('css/components.css')) ?>" rel="stylesheet">
    <?php foreach (($pageStyles ?? []) as $style): ?>
        <link href="<?= e(url($style)) ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
