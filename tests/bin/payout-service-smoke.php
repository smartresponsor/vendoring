<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$required = [
    'src/Service/VendorPayoutEntity/VendorPayoutService.php',
    'tests/Unit/VendorPayoutEntity/VendorPayoutServiceTest.php',
];

foreach ($required as $relativePath) {
    if (!is_file($root . '/' . $relativePath)) {
        fwrite(STDERR, '[FAIL] missing required file: ' . $relativePath . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] payout service smoke surface is present' . PHP_EOL);
