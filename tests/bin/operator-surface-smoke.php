<?php

declare(strict_types=1);

$requiredFiles = [
    dirname(__DIR__, 2).'/src/Controller/Ops/VendorTransactionOperatorController.php',
    dirname(__DIR__, 2).'/src/Service/Ops/VendorTransactionOperatorPageBuilder.php',
    dirname(__DIR__, 2).'/tests/Integration/Runtime/VendorTransactionOperatorSurfaceTest.php',
    dirname(__DIR__, 2).'/docs/release/RC_OPERATOR_SURFACE.md',
];

foreach ($requiredFiles as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf('Missing operator surface artifact: %s%s', $requiredFile, PHP_EOL));
        exit(1);
    }
}

echo "operator surface smoke OK\n";
