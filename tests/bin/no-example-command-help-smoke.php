<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    $root . '/src/Command/VendorApiKeyCreateCommand.php',
    $root . '/src/Command/VendorApiKeyListCommand.php',
    $root . '/src/Command/VendorApiKeyRotateCommand.php',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing command file: {$file}\n");
        exit(1);
    }

    $content = (string) file_get_contents($file);
    if (str_contains($content, "->setHelp('Example:")) {
        fwrite(STDERR, "Example help wording found in {$file}\n");
        exit(1);
    }
}

$composerJson = $root . '/composer.json';
$content = (string) file_get_contents($composerJson);
foreach (['test:no-example-command-help', '@test:no-example-command-help'] as $needle) {
    if (!str_contains($content, $needle)) {
        fwrite(STDERR, "Missing composer wiring: {$needle}\n");
        exit(1);
    }
}

echo "no-example-command-help smoke passed\n";
