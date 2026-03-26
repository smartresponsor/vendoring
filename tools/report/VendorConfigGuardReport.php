<?php

declare(strict_types=1);

require __DIR__.'/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor config guard report');
$root = vendorReportProjectRoot();

$requiredFiles = [
    'config/bundles.php',
    'config/packages/framework.yaml',
    'config/packages/doctrine.yaml',
    'config/vendor_services.yaml',
    'config/vendor_routes.yaml',
    'config/services_runtime.php',
    'config/routes_runtime.php',
];

$hasWarning = false;
foreach ($requiredFiles as $relativePath) {
    $ok = vendorReportHasNonEmptyFile($relativePath);
    vendorReportPrintCheck($relativePath, $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

$configFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/config', FilesystemIterator::SKIP_DOTS)
);

$ignoredConfigFiles = [
    $root.'/config/reference.php',
];

$forbiddenNeedles = ['example', 'stub', 'todo'];
foreach ($configFiles as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $pathname = str_replace('\\', '/', $file->getPathname());
    if (in_array($pathname, array_map(static fn (string $path): string => str_replace('\\', '/', $path), $ignoredConfigFiles), true)) {
        continue;
    }

    $contents = strtolower((string) file_get_contents($file->getPathname()));
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            vendorReportPrintCheck('config contains '.$needle, false, str_replace($root.'/', '', $file->getPathname()));
            $hasWarning = true;
            break;
        }
    }
}

exit($hasWarning ? 1 : 0);
