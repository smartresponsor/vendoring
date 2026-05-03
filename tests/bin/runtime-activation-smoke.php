<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$required = [
    'config/routes_runtime.php',
    'config/routes/vendor_nelmio_api_doc.yaml',
    'templates/ops/vendor_transactions/index.html.twig',
    'src/DTO/Ops/VendorTransactionCreateInputDTO.php',
    'src/Form/Ops/VendorTransactionCreateForm.php',
    'src/DTO/Ops/VendorTransactionStatusUpdateInputDTO.php',
    'src/Form/Ops/VendorTransactionStatusUpdateForm.php',
    'docs/release/RC_RUNTIME_ACTIVATION.md',
];

$forbidden = [
    'config/packages_runtime.php',
    'config/services_runtime.php',
    'config/vendor_services.yaml',
];

foreach ($required as $path) {
    if (!is_file($root . '/' . $path)) {
        fwrite(STDERR, sprintf('Missing runtime activation artifact: %s
', $path));
        exit(1);
    }
}

foreach ($forbidden as $path) {
    if (file_exists($root . '/' . $path)) {
        fwrite(STDERR, sprintf('Legacy runtime activation artifact must not exist: %s
', $path));
        exit(1);
    }
}

echo 'Runtime activation smoke OK
';
