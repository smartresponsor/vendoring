<?php

declare(strict_types=1);

require __DIR__.'/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor config drift report');

$pairs = [
    ['config/packages/nelmio_api_doc.yaml.dist', 'config/routes/nelmio_api_doc.yaml.dist'],
    ['config/services.yaml', 'config/services_runtime.php'],
    ['config/routes.yaml', 'config/routes_runtime.php'],
];

$hasWarning = false;
foreach ($pairs as [$left, $right]) {
    $leftExists = vendorReportFileExists($left);
    $rightExists = vendorReportFileExists($right);
    $ok = $leftExists && $rightExists;
    vendorReportPrintCheck($left.' <-> '.$right, $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

exit($hasWarning ? 1 : 0);
