<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('Vendoring mirror enforcer report');
$root = vendorReportProjectRoot();

/**
 * @return list<array{implementation:string, contract:string}>
 */
function vendorMirrorPairs(string $contractRoot, string $implementationRoot, string $contractNamespace, string $implementationNamespace): array
{
    $root = vendorReportProjectRoot();
    $absoluteContractRoot = $root . '/' . $contractRoot;

    if (!is_dir($absoluteContractRoot)) {
        return [];
    }

    $pairs = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absoluteContractRoot, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
            continue;
        }

        $contractPath = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $relative = substr($contractPath, strlen($contractRoot) + 1);
        $implementationRelative = preg_replace('/Interface\.php$/', '.php', $relative) ?? $relative;
        $implementationPath = $implementationRoot . '/' . $implementationRelative;

        $contractCode = (string) file_get_contents($file->getPathname());
        $implementationCode = is_file($root . '/' . $implementationPath)
            ? (string) file_get_contents($root . '/' . $implementationPath)
            : '';

        $expectedContractNamespace = $contractNamespace;
        $contractDir = str_replace('\\', '/', dirname($relative));
        if ('.' !== $contractDir && '' !== $contractDir) {
            $expectedContractNamespace .= '\\' . str_replace('/', '\\', $contractDir);
        }

        $expectedImplementationNamespace = $implementationNamespace;
        $implementationDir = str_replace('\\', '/', dirname($implementationRelative));
        if ('.' !== $implementationDir && '' !== $implementationDir) {
            $expectedImplementationNamespace .= '\\' . str_replace('/', '\\', $implementationDir);
        }

        $contractOk = preg_match('/^namespace\s+' . preg_quote($expectedContractNamespace, '/') . '\s*;/m', $contractCode) === 1;
        $implementationOk = is_file($root . '/' . $implementationPath)
            && preg_match('/^namespace\s+' . preg_quote($expectedImplementationNamespace, '/') . '\s*;/m', $implementationCode) === 1;

        $pairs[] = [
            'implementation' => $implementationPath,
            'contract' => $contractPath,
            'ok' => $contractOk && $implementationOk,
        ];
    }

    usort($pairs, static fn(array $a, array $b): int => strcmp($a['contract'], $b['contract']));

    return $pairs;
}

$checks = array_merge(
    vendorMirrorPairs('src/RepositoryInterface', 'src/Repository', 'App\\Vendoring\\RepositoryInterface', 'App\\Vendoring\\Repository'),
    vendorMirrorPairs('src/ServiceInterface', 'src/Service', 'App\\Vendoring\\ServiceInterface', 'App\\Vendoring\\Service'),
    vendorMirrorPairs('src/PolicyInterface', 'src/Policy', 'App\\Vendoring\\PolicyInterface', 'App\\Vendoring\\Policy'),
);

$hasWarning = false;
foreach ($checks as $check) {
    $ok = (bool) $check['ok'];
    vendorReportPrintCheck($check['implementation'] . ' <-> ' . $check['contract'], $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

if ([] === $checks) {
    vendorReportPrintCheck('mirror contracts discovered', false, 'no mirror contracts found');
    $hasWarning = true;
}

exit($hasWarning ? 1 : 0);
