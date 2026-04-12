<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor PHP surface report');
exit(vendorReportRunScript('tools/qa/VendoringPhpLint.php'));
