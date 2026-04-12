<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$gitignore = is_file($root . '/.gitignore') ? (string) file_get_contents($root . '/.gitignore') : '';

if (!str_contains($gitignore, '.idea/')) {
    fwrite(STDERR, "IDE project directory must be ignored by git when present in a local current slice\n");
    exit(1);
}

if (!str_contains($gitignore, '*.iml')) {
    fwrite(STDERR, "IDE module files must be ignored by git when present in a local current slice\n");
    exit(1);
}

echo "idea-module-artifact smoke passed\n";
