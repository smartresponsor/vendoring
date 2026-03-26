<?php

declare(strict_types=1);

require __DIR__.'/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor contract report');

vendorReportSection('Repository contract smoke');
$repositoryExitCode = vendorReportRunScript('tests/bin/repository-contract-smoke.php');

vendorReportSection('Interface alias smoke');
$aliasExitCode = vendorReportRunScript('tests/bin/interface-alias-smoke.php');

exit(0 !== $repositoryExitCode || 0 !== $aliasExitCode ? 1 : 0);
