<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routes = (string) file_get_contents($root . '/config/vendor_routes.yaml');
$transactionServices = (string) file_get_contents($root . '/config/vendor_services_transactions.yaml');
$controller = (string) file_get_contents($root . '/src/Controller/Vendor/VendorTransactionController.php');

if (str_contains($routes, 'routes_vendor_transactions.yaml')) {
    fwrite(STDERR, "Legacy transaction route import must be removed.\n");
    exit(1);
}

if (!str_contains($controller, '/vendor/{vendorId}/{id}/status')) {
    fwrite(STDERR, "VendorTransactionController must use vendor-scoped status route.\n");
    exit(1);
}

if (str_contains($transactionServices, '$repo:')) {
    fwrite(STDERR, "vendor_services_transactions.yaml must not declare stale repo constructor argument.\n");
    exit(1);
}

fwrite(STDOUT, "transaction route smoke passed\n");
