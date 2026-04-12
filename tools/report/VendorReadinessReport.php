<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor readiness report');

$checks = [
    'OpenAPI generator' => 'bin/generate-openapi.php',
    'OpenAPI artifact json' => 'build/docs/openapi.json',
    'OpenAPI artifact yaml' => 'build/docs/openapi.yaml',
    'phpDocumentor generator' => 'bin/generate-phpdocumentor-site.php',
    'phpDocumentor config' => 'phpdoc.dist.xml',
    'RC evidence generator' => 'bin/generate-rc-evidence.php',
    'RC evidence json' => 'build/release/rc-evidence.json',
    'RC evidence markdown' => 'build/release/rc-evidence.md',
    'RC baseline docs' => 'docs/release/RC_BASELINE.md',
    'RC runtime docs' => 'docs/release/RC_RUNTIME_SURFACES.md',
    'RC CI docs' => 'docs/release/RC_CI_LANES.md',
];

$hasWarning = false;
foreach ($checks as $label => $path) {
    $ok = vendorReportHasNonEmptyFile($path);
    vendorReportPrintCheck($label, $ok, $path);
    if (!$ok) {
        $hasWarning = true;
    }
}

exit($hasWarning ? 1 : 0);
