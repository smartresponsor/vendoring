<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];
$requiredScripts = ['test:entrypoint'];

foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        fwrite(STDERR, sprintf("Missing composer script: %s\n", $scriptName));
        exit(1);
    }
}

$controller = (string) file_get_contents($root.'/src/Controller/VendorTransactionController.php');
if (!str_contains($controller, 'extends AbstractController')) {
    fwrite(STDERR, "VendorTransactionController must extend AbstractController.\n");
    exit(1);
}

if (!str_contains($controller, '#[Route(')) {
    fwrite(STDERR, "VendorTransactionController must declare Route attributes.\n");
    exit(1);
}

if (!str_contains($controller, 'VendorTransactionRepositoryInterface')) {
    fwrite(STDERR, "VendorTransactionController must depend on VendorTransactionRepositoryInterface.\n");
    exit(1);
}

fwrite(STDOUT, "entrypoint contract smoke passed\n");

if (!str_contains($controller, '/vendor/{vendorId}/{id}/status')) {
    fwrite(STDERR, "VendorTransactionController must use vendor-scoped status route.\n");
    exit(1);
}
