<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$bundles = (string) file_get_contents($root . '/config/bundles.php');
$routesRuntime = (string) file_get_contents($root . '/config/routes_runtime.php');
$routes = (string) file_get_contents($root . '/config/routes/vendor_nelmio_api_doc.yaml');
$package = (string) file_get_contents($root . '/config/packages/nelmio_api_doc.yaml');

$checks = [
    [str_contains($bundles, 'Nelmio\\ApiDocBundle\\NelmioApiDocBundle::class'), 'bundles.php must register NelmioApiDocBundle'],
    [str_contains($routesRuntime, "routes->import(__DIR__.'/routes/vendor_nelmio_api_doc.yaml');"), 'routes_runtime.php must import vendor_nelmio_api_doc.yaml'],
    [str_contains($routes, 'resource: \"@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml\"') || str_contains($routes, "resource: '@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml'"), 'vendor_nelmio_api_doc.yaml must import Nelmio swagger UI routing'],
    [str_contains($routes, 'prefix: /api/doc'), 'vendor_nelmio_api_doc.yaml must expose /api/doc prefix'],
    [str_contains($package, 'title: \"Vendoring API\"') || str_contains($package, "title: 'Vendoring API'"), 'nelmio_api_doc.yaml must define API title'],
    [str_contains($package, "version: '1.0.0-rc'") || str_contains($package, 'version: \"1.0.0-rc\"'), 'nelmio_api_doc.yaml must define RC version'],
    [str_contains($package, '^/api(?!/doc$)'), 'nelmio_api_doc.yaml must exclude /api/doc from documented path patterns'],
];

foreach ($checks as [$ok, $message]) {
    if (true !== $ok) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

echo 'api doc contract smoke OK' . PHP_EOL;
