<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('Vendoring quality residue report');
$root = vendorReportProjectRoot();
$scanRoots = [$root . '/src', $root . '/tests', $root . '/config'];
$literalNeedles = ['TODO', 'FIXME'];
$regexNeedles = [
    '/\bvar_dump\s*\(/',
    '/\bdd\s*\(/',
    '/\bdump\s*\(/',
    '/\bprint_r\s*\(/',
];
$hits = [];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        if (!in_array($file->getExtension(), ['php', 'yaml', 'yml'], true)) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        foreach ($literalNeedles as $needle) {
            if (str_contains($contents, $needle)) {
                $hits[] = str_replace($root . '/', '', $file->getPathname()) . ' :: ' . $needle;
            }
        }

        foreach ($regexNeedles as $needle) {
            if (1 === preg_match($needle, $contents)) {
                $hits[] = str_replace($root . '/', '', $file->getPathname()) . ' :: ' . $needle;
            }
        }
    }
}

if ([] === $hits) {
    vendorReportPrintCheck('No obvious residue markers found', true);
    exit(0);
}

foreach ($hits as $hit) {
    vendorReportPrintCheck('Residue marker', false, $hit);
}

exit(1);
