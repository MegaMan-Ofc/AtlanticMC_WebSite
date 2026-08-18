<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

foreach ([
    'unit/Core/CoreTest.php',
    'unit/Core/ArchitectureTest.php',
    'unit/Integrations/TebexTest.php',
    'unit/Core/RoutingTest.php',
    'unit/Core/MediaTest.php',
    'unit/Store/ProductDiscountTest.php',
    'unit/Store/RecommendedProductsTest.php',
    'unit/Store/HomeCategoryLayoutTest.php',
    'unit/Admin/HomeBannerCustomizationTest.php',
    'unit/Site/AboutSectionTest.php',
    'unit/Site/FaqTest.php',
    'unit/Site/NotFoundPageTest.php',
    'unit/Admin/MaintenanceModeTest.php',
    'unit/Store/ProductSearchTest.php',
    'unit/Store/CartAjaxTest.php',
    'unit/Admin/AdminAnalyticsTest.php',
    'unit/Core/SiteHardeningTest.php',
    'unit/Admin/AdminFiltersTest.php',
    'unit/Site/FrontendTest.php',
    'integration/Database/DatabaseTest.php',
] as $testFile) {
    require __DIR__ . '/' . $testFile;
}

@unlink($databasePath);
@unlink($maintenanceStatePath);
@unlink($appLogPath);
@unlink($securityLogPath);

exit($suite->finish());
