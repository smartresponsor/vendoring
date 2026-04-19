<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$servicesYaml = $root . '/config/vendor_services.yaml';

if (!is_file($servicesYaml)) {
    fwrite(STDERR, "vendor_services.yaml not found\n");
    exit(1);
}

$yaml = (string) file_get_contents($servicesYaml);
$errors = [];

/**
 * @return list<array{0:string,1:string}>
 */
$collectPairs = static function (string $interfaceRoot, string $implementationRoot, string $interfaceNs, string $implementationNs) use ($root): array {
    $pairs = [];
    $directory = $root . '/' . $interfaceRoot;

    if (!is_dir($directory)) {
        return $pairs;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (!$file->isFile() || 'php' !== $file->getExtension()) {
            continue;
        }

        $relativePath = substr($file->getPathname(), strlen($directory) + 1);
        $relativeClass = str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relativePath);
        $implementationRelativeClass = str_ends_with($relativeClass, 'Interface')
            ? substr($relativeClass, 0, -strlen('Interface'))
            : $relativeClass;
        $implementationPath = $root . '/' . $implementationRoot . '/' . str_replace('\\', '/', $implementationRelativeClass) . '.php';

        if (!is_file($implementationPath)) {
            continue;
        }

        $pairs[] = [$interfaceNs . '\\' . $relativeClass, $implementationNs . '\\' . $implementationRelativeClass];
    }

    sort($pairs);

    return $pairs;
};

$pairs = array_merge(
    $collectPairs('src/ServiceInterface', 'src/Service', 'App\Vendoring\\ServiceInterface', 'App\Vendoring\\Service'),
    $collectPairs('src/RepositoryInterface', 'src/Repository', 'App\Vendoring\\RepositoryInterface', 'App\Vendoring\\Repository'),
);

foreach ($pairs as [$interfaceClass, $implementationClass]) {
    $alias = sprintf("%s: '@%s'", $interfaceClass, $implementationClass);

    if (!str_contains($yaml, $alias)) {
        $errors[] = $alias;
    }
}

if ([] !== $errors) {
    fwrite(STDERR, "Missing canonical aliases in config/vendor_services.yaml:\n");

    foreach ($errors as $error) {
        fwrite(STDERR, ' - ' . $error . "\n");
    }

    exit(1);
}

fwrite(STDOUT, sprintf("Alias audit passed: %d aliases verified.\n", count($pairs)));
exit(0);
