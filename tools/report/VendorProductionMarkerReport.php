<?php

declare(strict_types=1);

require __DIR__.'/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor production marker report');

$required = [
    'README.md',
    'RELEASE_NOTES.md',
    'docs/release/RC_BASELINE.md',
    'docs/release/RC_RUNTIME_ACTIVATION.md',
    'docs/release/RC_OPERATOR_SURFACE.md',
    'docs/release/RC_OPENAPI_SURFACE.md',
    'docs/release/RC_PHPDOCUMENTOR_SURFACE.md',
];

$hasWarning = false;
foreach ($required as $relativePath) {
    $ok = vendorReportHasNonEmptyFile($relativePath);
    vendorReportPrintCheck($relativePath, $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

exit($hasWarning ? 1 : 0);
