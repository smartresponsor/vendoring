<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requiredFiles = [
    $root.'/src/Service/Http/Vendor/Transaction/Operator/VendorTransactionOperatorService.php',
    $root.'/tests/Integration/Runtime/VendorTransactionOperatorSurfaceTest.php',
    $root.'/docs/release/RC_OPERATOR_SURFACE.md',
];

foreach ($requiredFiles as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf('Missing operator surface artifact: %s%s', $requiredFile, PHP_EOL));
        exit(1);
    }
}

$service = (string) file_get_contents($root.'/src/Service/Http/Vendor/Transaction/Operator/VendorTransactionOperatorService.php');
if (!str_contains($service, 'namespace App\\Vendoring\\Service\\Http\\Vendor\\Transaction\\Operator;')) {
    fwrite(STDERR, 'Operator surface must live under App\\Vendoring\\Service\\Http\\Vendor\\Transaction\\Operator.'.PHP_EOL);
    exit(1);
}

if (str_contains($service, '#[Route(')) {
    fwrite(STDERR, 'Operator surface service must not declare route attributes.'.PHP_EOL);
    exit(1);
}

echo "operator surface smoke OK\n";
