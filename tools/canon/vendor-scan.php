<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendoring canon guard (fast scan).
 *
 * Checks:
 * - no repeated path segments under src/
 * - namespace/path consistency for App\Vendoring classes
 * - literal Layer 3 Vendor bucket contract for Controller/Event/Policy/Repository layers
 * - no mixed src/Security, src/Observability, src/Support, or src/Command/Support buckets
 * - Symfony console commands are named Vendor*Command.php
 * - service layers use direction folders and Vendor*Service / Vendor*ServiceInterface names
 */

$root = dirname(__DIR__, 2);
$src = $root . '/src';
if (!is_dir($src)) {
    fwrite(STDERR, "src/ not found\n");
    exit(2);
}

$issues = [];

$layerContracts = [
    'Controller' => ['Vendor', '/^Vendor.*Controller\.php$/'],
    'ControllerInterface' => ['Vendor', '/^Vendor.*ControllerInterface\.php$/'],
    'Event' => ['Vendor', '/^Vendor.*Event\.php$/'],
    'EventInterface' => ['Vendor', '/^Vendor.*EventInterface\.php$/'],
    'Policy' => ['Vendor', '/^Vendor.*Policy\.php$/'],
    'PolicyInterface' => ['Vendor', '/^Vendor.*PolicyInterface\.php$/'],
    'Repository' => ['Vendor', '/^Vendor.*Repository\.php$/'],
    'RepositoryInterface' => ['Vendor', '/^Vendor.*RepositoryInterface\.php$/'],
];

foreach ($layerContracts as $layer => [$onlyChild, $filePattern]) {
    $layerPath = $src . '/' . $layer;
    if (!is_dir($layerPath)) {
        continue;
    }

    $entries = array_values(array_filter(scandir($layerPath) ?: [], static fn(string $entry): bool => $entry !== '.' && $entry !== '..'));
    foreach ($entries as $entry) {
        $entryPath = $layerPath . '/' . $entry;
        if ($entry !== $onlyChild) {
            $issues[] = "layer3_forbidden_entry\t{$entryPath}\t{$layer} allows only {$onlyChild}";
            continue;
        }
        if (!is_dir($entryPath)) {
            $issues[] = "layer3_vendor_not_directory\t{$entryPath}";
        }
    }

    $vendorPath = $layerPath . '/' . $onlyChild;
    if (!is_dir($vendorPath)) {
        $issues[] = "layer3_missing_vendor_bucket\t{$vendorPath}";
        continue;
    }

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendorPath, FilesystemIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        /** @var SplFileInfo $file */
        $path = str_replace('\\', '/', $file->getPathname());
        if ($file->isDir()) {
            continue;
        }
        if ($file->getExtension() !== 'php') {
            $issues[] = "layer3_forbidden_non_php\t{$path}";
            continue;
        }
        $relativeInsideVendor = trim(str_replace(str_replace('\\', '/', $vendorPath), '', $path), '/');
        if (str_contains($relativeInsideVendor, '/')) {
            $issues[] = "layer3_forbidden_subdirectory\t{$path}\t{$layer}/Vendor must be flat";
            continue;
        }
        if (preg_match($filePattern, basename($path)) !== 1) {
            $issues[] = "layer3_bad_filename\t{$path}\tpattern={$filePattern}";
        }
    }
}

$forbiddenBuckets = [
    'Security' => 'use Service/Security, ServiceInterface/Security, Voter, Authenticator, Subscriber, Listener, or Middleware',
    'Observability' => 'use Service/Observability, ServiceInterface/Observability, Subscriber/Observability, Listener, or Middleware',
    'Support' => 'use explicit type layers such as Service/Runtime, DTO, ValueObject, Enum, or Exception',
    'Command/Support' => 'use Service/Command, ServiceInterface/Command, DTO/Command, Enum/Command, or Exception/Command',
];
foreach ($forbiddenBuckets as $bucket => $message) {
    if (is_dir($src . '/' . $bucket)) {
        $issues[] = "layer3_forbidden_bucket\t{$src}/{$bucket}\t{$message}";
    }
}

$commandPath = $src . '/Command';
if (is_dir($commandPath)) {
    foreach (glob($commandPath . '/*.php') ?: [] as $commandFile) {
        $commandBase = basename($commandFile);
        if (preg_match('/^Vendor.*Command\.php$/', $commandBase) !== 1) {
            $issues[] = "command_bad_filename\t{$commandFile}\tpattern=/^Vendor.*Command\\.php$/";
        }
    }
}


$serviceContracts = [
    'Service' => '/^Vendor.*Service\.php$/',
    'ServiceInterface' => '/^Vendor.*ServiceInterface\.php$/',
];

foreach ($serviceContracts as $serviceLayer => $servicePattern) {
    $serviceLayerPath = $src . '/' . $serviceLayer;
    if (!is_dir($serviceLayerPath)) {
        continue;
    }

    foreach (glob($serviceLayerPath . '/*.php') ?: [] as $rootServiceFile) {
        $issues[] = "service_root_php_forbidden\t{$rootServiceFile}\t{$serviceLayer} requires direction folders";
    }

    $serviceIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serviceLayerPath, FilesystemIterator::SKIP_DOTS));
    foreach ($serviceIterator as $serviceFile) {
        /** @var SplFileInfo $serviceFile */
        if (!$serviceFile->isFile() || $serviceFile->getExtension() !== 'php') {
            continue;
        }

        $relativeServicePath = trim(str_replace($serviceLayerPath, '', $serviceFile->getPathname()), DIRECTORY_SEPARATOR);
        if (!str_contains(str_replace('\\', '/', $relativeServicePath), '/')) {
            continue;
        }

        $serviceBase = basename($serviceFile->getPathname());
        if (preg_match($servicePattern, $serviceBase) !== 1) {
            $issues[] = "service_bad_filename\t{$serviceFile->getPathname()}\tpattern={$servicePattern}";
        }
    }
}

$enumPath = $src . '/Enum';
if (is_dir($enumPath)) {
    $enumIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($enumPath, FilesystemIterator::SKIP_DOTS));
    foreach ($enumIterator as $enumFile) {
        /** @var SplFileInfo $enumFile */
        if (!$enumFile->isFile() || $enumFile->getExtension() !== 'php') {
            continue;
        }
        $enumBase = basename($enumFile->getPathname());
        if (preg_match('/^Vendor.*Enum\\.php$/', $enumBase) !== 1) {
            $issues[] = "enum_bad_filename\t{$enumFile->getPathname()}\tpattern=/^Vendor.*Enum\\.php$/";
        }
    }
}

$apiExceptionPath = $src . '/Exception/Api';
if (is_dir($apiExceptionPath)) {
    foreach (glob($apiExceptionPath . '/*.php') ?: [] as $exceptionFile) {
        $exceptionBase = basename($exceptionFile);
        if (preg_match('/^Vendor.*Exception\.php$/', $exceptionBase) !== 1) {
            $issues[] = "exception_api_bad_filename\t{$exceptionFile}\tpattern=/^Vendor.*Exception\\.php$/";
        }
    }
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $segments = explode('/', trim(str_replace($src, '', $path), '/'));
    for ($i = 1; $i < count($segments); $i++) {
        if ($segments[$i] !== '' && $segments[$i] === $segments[$i - 1]) {
            $issues[] = "repeat_segment\t{$path}\t{$segments[$i - 1]}/{$segments[$i]}";
            break;
        }
    }

    if (!str_ends_with($path, '.php')) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        $issues[] = "read_fail\t{$path}";
        continue;
    }

    if (!preg_match('/^\s*namespace\s+([^;]+);/m', $content, $m)) {
        continue;
    }

    $ns = trim($m[1]);
    if (!str_starts_with($ns, 'App\\Vendoring\\')) {
        continue;
    }

    $rel = trim(str_replace($src . '/', '', $path), '/');
    $relNoExt = preg_replace('/\.php$/', '', $rel);
    $expected = 'App\\Vendoring\\' . str_replace('/', '\\', $relNoExt);
    $base = basename($relNoExt);

    if (preg_match('/\b(class|interface|trait|enum)\s+' . preg_quote($base, '/') . '\b/', $content) !== 1) {
        continue;
    }

    $declared = $ns . '\\' . $base;
    if ($declared !== $expected) {
        $issues[] = "ns_path_mismatch\t{$path}\tdeclared={$declared}\texpected={$expected}";
    }
}

if ($issues !== []) {
    foreach ($issues as $line) {
        echo $line, "\n";
    }
    fwrite(STDERR, 'Vendoring canon scan: FAIL (' . count($issues) . " issue(s))\n");
    exit(1);
}

echo "Vendoring canon scan: OK\n";

// DTO / Form / ValueObject canon: DTO=Vendor*DTO.php, Form=Vendor*Form.php, ValueObject=Vendor*ValueObject.php.
