<?php

declare(strict_types=1);

$modules = require $root . '/app/modules.php';
$modulePaths = array_map(static fn (string $module): string => $root . '/app/' . $module, $modules);

$assert(
    is_dir($root . '/app/Admin')
        && is_dir($root . '/app/Core')
        && is_dir($root . '/app/Store')
        && is_dir($root . '/app/Site')
        && is_dir($root . '/app/Integrations')
        && !is_dir($root . '/includes'),
    'Application code is grouped by domain instead of a flat includes directory.'
);

$assert(
    count($modules) === count(array_unique($modules))
        && array_reduce($modulePaths, static fn (bool $valid, string $path): bool => $valid && is_file($path), true),
    'The application module manifest contains unique existing modules.'
);

$bootstrapSource = file_get_contents($root . '/app/bootstrap.php');
$assert(
    is_string($bootstrapSource)
        && str_contains($bootstrapSource, "require __DIR__ . '/modules.php'")
        && !str_contains($bootstrapSource, "require_once __DIR__ . '/Core/")
        && !str_contains($bootstrapSource, "require_once __DIR__ . '/Admin/"),
    'Bootstrap loads the centralized module manifest instead of maintaining duplicate dependency lists.'
);

$assert(
    is_file($root . '/controllers/Admin/admin.php')
        && is_file($root . '/controllers/Store/catalog.php')
        && is_file($root . '/controllers/Site/home.php')
        && is_file($root . '/templates/admin/catalog/products/index.php')
        && is_file($root . '/templates/admin/commerce/orders/index.php')
        && is_file($root . '/templates/layout/head.php')
        && is_file($root . '/templates/store/catalog.php'),
    'Controllers and views follow the same domain-oriented organization as application modules.'
);
