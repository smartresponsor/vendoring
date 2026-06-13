<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeRoot = $root.'/config/platform/routes';

$errors = [];
$services = [];
$types = [];

if (!is_dir($routeRoot)) {
    fwrite(STDERR, "Missing config/platform/routes.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($routeRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || 'yaml' !== $file->getExtension()) {
        continue;
    }

    $contents = (string) file_get_contents($file->getPathname());

    if (preg_match_all('/service:\s*([^,\}\s]+)/', $contents, $matches) > 0) {
        foreach ($matches[1] as $fqcn) {
            if (str_starts_with($fqcn, 'App\\Vendoring\\Service\\Http\\Vendor\\')) {
                $services[$fqcn] = true;
            }
        }
    }

    if (preg_match_all('/type:\s*([^,\}\s]+)/', $contents, $matches) > 0) {
        foreach ($matches[1] as $fqcn) {
            if (str_starts_with($fqcn, 'App\\Vendoring\\Form\\Vendor\\')) {
                $types[$fqcn] = true;
            }
        }
    }
}

ksort($services);
ksort($types);

if ([] === $services) {
    fwrite(STDERR, "No route-map services discovered; route-map parser regex or registry path is broken.\n");
    exit(1);
}

$lines = [
    '# Vendoring Runtime Artifact Inventory',
    '',
    'Generated expectation from `config/platform/routes/**/*.yaml`.',
    '',
    '## Services',
    '',
];

foreach (array_keys($services) as $fqcn) {
    $lines[] = '- `'.$fqcn.'`';
}

$lines[] = '';
$lines[] = '## Types';
$lines[] = '';

foreach (array_keys($types) as $fqcn) {
    $lines[] = '- `'.$fqcn.'`';
}

$inventoryPath = $root.'/docs/runtime-artifact-inventory.md';
$current = implode("\n", $lines)."\n";

if (!file_exists($inventoryPath)) {
    file_put_contents($inventoryPath, $current);
    echo "Vendor runtime artifact inventory created.\n";
    exit(0);
}

$existing = (string) file_get_contents($inventoryPath);
if ($existing !== $current) {
    file_put_contents($inventoryPath, $current);
    fwrite(STDERR, "Vendor runtime artifact inventory was stale and has been regenerated. Re-run the smoke.\n");
    exit(1);
}

echo sprintf(
    "Vendor runtime artifact inventory smoke passed: %d services, %d types.\n",
    count($services),
    count($types)
);
