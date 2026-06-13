<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$routeRegistry = $root.'/config/platform/routes.crud.yaml';
$transactionServices = $root.'/config/vendor_services_transactions.yaml';
$transactionHttpService = $root.'/src/Service/Http/Vendor/Transaction/VendorTransactionHttpService.php';

foreach ([$routeRegistry, $transactionServices, $transactionHttpService] as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf("Missing required transaction artifact: %s\n", substr($requiredFile, strlen($root) + 1)));
        exit(1);
    }
}

$routes = (string) file_get_contents($routeRegistry);
$services = (string) file_get_contents($transactionServices);
$httpService = (string) file_get_contents($transactionHttpService);

if (str_contains($routes, 'routes_vendor_transactions.yaml')) {
    fwrite(STDERR, "Legacy transaction route import must be absent from platform route registry.\n");
    exit(1);
}

if (!str_contains($httpService, 'public function updateStatus(string $vendorId, int $id, Request $request)')) {
    fwrite(STDERR, "VendorTransactionHttpService must keep vendor-scoped status update contract.\n");
    exit(1);
}

if (str_contains($services, '$repo:')) {
    fwrite(STDERR, "vendor_services_transactions.yaml must not declare stale repo constructor argument.\n");
    exit(1);
}

fwrite(STDOUT, "transaction route smoke passed\n");
