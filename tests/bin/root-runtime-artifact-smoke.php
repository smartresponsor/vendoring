<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$gitignore = is_file($root.'/.gitignore') ? (string) file_get_contents($root.'/.gitignore') : '';

if (!str_contains($gitignore, '.php-cs-fixer.cache')) {
    fwrite(STDERR, "Local php-cs-fixer cache artifacts must be ignored by git when present in a current slice\n");
    exit(1);
}

if (!str_contains($gitignore, '*.log')) {
    fwrite(STDERR, "Operational action logs under canonical tooling dot-folders must be ignored by git when present in a current slice\n");
    exit(1);
}

fwrite(STDOUT, "[OK] local runtime artifacts are ignored by git\n");
