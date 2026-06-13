<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeMap = $root.'/config/platform/routes/business/vendor.yaml';

if (!file_exists($routeMap)) {
    fwrite(STDERR, "Missing vendor business route map.\n");
    exit(1);
}

$contents = (string) file_get_contents($routeMap);
preg_match_all('/service:\s*([^,}\s]+)/', $contents, $serviceMatches);
preg_match_all('/type:\s*([^,}\s]+)/', $contents, $typeMatches);

$missing = [];
foreach (array_unique($serviceMatches[1] ?? []) as $fqcn) {
    $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
    if (!file_exists($path)) {
        $missing[] = 'Missing service: '.$fqcn.' at '.$path;
    }
}
foreach (array_unique($typeMatches[1] ?? []) as $fqcn) {
    $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
    if (!file_exists($path)) {
        $missing[] = 'Missing type: '.$fqcn.' at '.$path;
    }
}
if ([] !== $missing) {
    fwrite(STDERR, "Vendor business route-map smoke failed:\n");
    foreach ($missing as $item) {
        fwrite(STDERR, ' - '.$item."\n");
    }
    exit(1);
}
echo "Vendor business route-map smoke passed.\n";
