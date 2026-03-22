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

$protocolArtifacts = array_values(array_filter(scandir($root) ?: [], static function (string $entry): bool {
    if ('.' === $entry || '..' === $entry) {
        return false;
    }

    return (bool) preg_match('/^[A-Z0-9_-]+_PROTOCOL_ANALYSIS\.md$/', $entry);
}));

if ([] !== $protocolArtifacts) {
    fwrite(STDERR, 'Root must not contain protocol analysis markdown files: '.implode(', ', $protocolArtifacts).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] root-level protocol analysis markdown artifacts are absent
');

if (is_dir($root.'/vendor')) {
    fwrite(STDERR, "Persistent vendor/ directory must not exist in cumulative source snapshot\n");
    exit(1);
}

fwrite(STDOUT, "[OK] persistent vendor directory is absent\n");
