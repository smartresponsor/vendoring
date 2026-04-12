<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor canonical structure report');

vendorReportSection('Structure scan');
$structureExitCode = vendorReportRunScript('tools/vendoring-structure-scan.php');

vendorReportSection('PSR-4 scan');
$psrExitCode = vendorReportRunScript('tools/vendoring-psr4-scan.php');

vendorReportSection('Service naming audit');
$serviceNamingExitCode = vendorReportRunScript('tools/vendoring-service-naming-audit.php');

exit(0 !== $structureExitCode || 0 !== $psrExitCode || 0 !== $serviceNamingExitCode ? 1 : 0);
