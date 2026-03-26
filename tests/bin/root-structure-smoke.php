<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$canonical = $root.'/src/Controller/VendorTransactionController.php';
$forbidden = $root.'/VendorTransactionController.php';

if (!is_file($canonical)) {
    fwrite(STDERR, "Missing canonical src/Controller/VendorTransactionController.php\n");
    exit(1);
}

if (is_file($forbidden)) {
    fwrite(STDERR, "Forbidden root-level VendorTransactionController.php still exists\n");
    exit(1);
}

$rootPhpFiles = glob($root.'/*.php') ?: [];
foreach ($rootPhpFiles as $path) {
    $name = basename($path);
    if (str_starts_with($name, '.')) {
        continue;
    }

    fwrite(STDERR, "Forbidden root-level non-dot PHP artifact found: {$name}\n");
    exit(1);
}

fwrite(STDOUT, "[OK] canonical root structure is enforced\n");

$waveArtifacts = array_values(array_filter(scandir($root) ?: [], static function (string $entry): bool {
    if ('.' === $entry || '..' === $entry) {
        return false;
    }

    return (bool) preg_match('/^vendoring-wave\d+.*\.md$/', $entry);
}));

if ([] !== $waveArtifacts) {
    fwrite(STDERR, 'Root must not contain wave artifact markdown files: '.implode(', ', $waveArtifacts).PHP_EOL);
    exit(1);
}

$gitignore = is_file($root.'/.gitignore') ? (string) file_get_contents($root.'/.gitignore') : '';

if (!str_contains($gitignore, 'PROTOCOL_ANALYSIS.md')) {
    fwrite(STDERR, "Local protocol analysis markdown artifacts must be ignored by git when present in a current slice\n");
    exit(1);
}

fwrite(STDOUT, "[OK] local protocol analysis markdown artifacts are ignored by git\n");

if (!str_contains($gitignore, '/vendor/')) {
    fwrite(STDERR, "Local vendor/ directory must be ignored by git when present in a current slice\n");
    exit(1);
}

fwrite(STDOUT, "[OK] local vendor directory is ignored by git\n");
