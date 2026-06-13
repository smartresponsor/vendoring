<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');

if (!array_key_exists('test:entrypoint', $scripts)) {
    fwrite(STDERR, "Missing composer script: test:entrypoint\n");
    exit(1);
}

$expectedServices = [
    'src/Service/Http/Vendor/Transaction/VendorTransactionHttpService.php',
    'src/Service/Http/Vendor/Transaction/Operator/VendorTransactionOperatorService.php',
    'src/Service/Http/Vendor/Summary/VendorSummaryHttpService.php',
    'src/Service/Http/Vendor/Metric/VendorMetricService.php',
    'src/Service/Http/Vendor/Payout/Account/VendorPayoutAccountService.php',
    'src/Service/Http/Vendor/Payout/VendorPayoutHttpService.php',
    'src/Service/Http/Vendor/Statement/VendorStatementHttpService.php',
    'src/Service/Http/Vendor/Statement/Export/VendorStatementExportService.php',
];

foreach ($expectedServices as $relativePath) {
    $path = $root.'/'.$relativePath;
    if (!is_file($path)) {
        fwrite(STDERR, sprintf("Missing canonical HTTP service: %s\n", $relativePath));
        exit(1);
    }

    $contents = (string) file_get_contents($path);
    if (!str_contains($contents, 'namespace App\\Vendoring\\Service\\Http\\Vendor')) {
        fwrite(STDERR, sprintf("HTTP service must use App\\Vendoring\\Service\\Http\\Vendor namespace: %s\n", $relativePath));
        exit(1);
    }

    if (str_contains($contents, '#[Route(') || str_contains($contents, 'Symfony\\Component\\Routing\\Attribute\\Route')) {
        fwrite(STDERR, sprintf("HTTP service must not declare Symfony Route attributes: %s\n", $relativePath));
        exit(1);
    }
}

if (is_dir($root.'/src/Controller') || is_dir($root.'/src/ControllerTrait')) {
    fwrite(STDERR, "Controller layer must not exist in zero-controller Vendoring.\n");
    exit(1);
}

fwrite(STDOUT, "entrypoint contract smoke passed\n");
