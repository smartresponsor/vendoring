<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$script = $root . '/.commanding/reorganize-tests.ps1';

if (!is_file($script)) {
    echo "no-legacy-vendor-script-smoke: skipped (no .commanding/reorganize-tests.ps1)\n";
    exit(0);
}

$content = (string) file_get_contents($script);
$legacyMarkers = [
    "'Vendor')",
    "'Vendor',",
    "keepSubdirs = @('Api','DTO','E2E','Form','Twig','Vendor')",
    '\\Vendor\\VendorEnTest.php',
];

foreach ($legacyMarkers as $marker) {
    if (str_contains($content, $marker)) {
        fwrite(STDERR, "Legacy Vendor keepSubdir marker still present\n");
        exit(1);
    }
}

echo "no-legacy-vendor-script-smoke: ok\n";
