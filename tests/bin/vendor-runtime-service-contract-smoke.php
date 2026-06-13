<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeRoot = $root.'/config/platform/routes';

$errors = [];

if (!is_dir($routeRoot)) {
    fwrite(STDERR, "Missing config/platform/routes.\n");
    exit(1);
}

$services = [];
$types = [];

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
    $errors[] = 'No route-map services were discovered; route-map parser regex or registry path is broken.';
}

function fqcn_to_file(string $root, string $fqcn): string
{
    if (!str_starts_with($fqcn, 'App\\')) {
        return '';
    }

    return $root.'/src/'.str_replace('\\', '/', substr($fqcn, 4)).'.php';
}

foreach (array_keys($services) as $fqcn) {
    $file = fqcn_to_file($root, $fqcn);

    if (!is_file($file)) {
        $errors[] = sprintf('Missing route-map service file for %s', $fqcn);
        continue;
    }

    $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
    $contents = (string) file_get_contents($file);
    $shortName = basename($file, '.php');

    if (!str_ends_with($shortName, 'Service')) {
        $errors[] = sprintf('%s must end with Service.php', $relative);
    }

    if (!str_contains($contents, 'namespace App\\Vendoring\\Service\\Http\\Vendor')) {
        $errors[] = sprintf('%s must use App\\Vendoring\\Service\\Http\\Vendor namespace', $relative);
    }

    if (!preg_match('/final\s+(?:readonly\s+)?class\s+'.preg_quote($shortName, '/').'\b/', $contents)) {
        $errors[] = sprintf('%s must define final class %s', $relative, $shortName);
    }

    if (!str_contains($contents, 'function __invoke(')) {
        $errors[] = sprintf('%s must expose __invoke() for Cruding FQCN convention', $relative);
    }

    if (1 === preg_match('/function\s+__invoke\s*\([^)]*\)\s*:\s*([^\\s{]+)/', $contents, $m)) {
        $returnType = trim($m[1]);
        $allowedReturnTypes = [
            'array',
            'JsonResponse',
            'Response',
            'RedirectResponse',
            'mixed',
        ];

        if (!in_array($returnType, $allowedReturnTypes, true)) {
            $errors[] = sprintf('%s has unexpected __invoke return type %s', $relative, $returnType);
        }
    } else {
        $errors[] = sprintf('%s must declare an explicit __invoke return type', $relative);
    }

    if (str_contains($contents, 'extends AbstractController') || str_contains($contents, '#[Route(')) {
        $errors[] = sprintf('%s must not contain controller/route vocabulary', $relative);
    }
}

foreach (array_keys($types) as $fqcn) {
    $file = fqcn_to_file($root, $fqcn);

    if (!is_file($file)) {
        $errors[] = sprintf('Missing route-map form type file for %s', $fqcn);
        continue;
    }

    $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
    $contents = (string) file_get_contents($file);
    $shortName = basename($file, '.php');

    if (!str_ends_with($shortName, 'Type')) {
        $errors[] = sprintf('%s must end with Type.php', $relative);
    }

    if (!str_contains($contents, 'namespace App\\Vendoring\\Form\\Vendor')) {
        $errors[] = sprintf('%s must use App\\Vendoring\\Form\\Vendor namespace', $relative);
    }

    if (!str_contains($contents, 'extends AbstractType')) {
        $errors[] = sprintf('%s must extend Symfony AbstractType', $relative);
    }
}

if ([] !== $errors) {
    fwrite(STDERR, "Vendor runtime service contract smoke failed:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }
    exit(1);
}

echo sprintf(
    "Vendor runtime service contract smoke passed: %d route-map services, %d route-map types checked.\n",
    count($services),
    count($types)
);
