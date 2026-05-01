<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$required = [
    'config/packages_runtime.php',
    'config/routes_runtime.php',
    'config/services_runtime.php',
    'config/routes/vendor_nelmio_api_doc.yaml',
    'templates/ops/vendor_transactions/index.html.twig',
    'src/Form/Ops/VendorTransactionCreateInputDTO.php',
    'src/Form/Ops/VendorTransactionCreateType.php',
    'src/Form/Ops/VendorTransactionStatusUpdateInputDTO.php',
    'src/Form/Ops/VendorTransactionStatusUpdateType.php',
    'docs/release/RC_RUNTIME_ACTIVATION.md',
];

foreach ($required as $path) {
    if (!is_file($root . '/' . $path)) {
        fwrite(STDERR, sprintf("Missing runtime activation artifact: %s\n", $path));
        exit(1);
    }
}

echo "Runtime activation smoke OK\n";
