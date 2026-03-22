<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    $root.'/ops/policy/config/crm.yaml' => 'provider: "stub"',
    $root.'/ops/policy/config/kms.yaml' => "provider: 'stub'",
];

foreach ($checks as $file => $needle) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing file: {$file}\n");
        exit(1);
    }

    $contents = (string) file_get_contents($file);

    if (str_contains($contents, $needle)) {
        fwrite(STDERR, "Forbidden stub provider remains in: {$file}\n");
        exit(1);
    }
}

echo "no-stub-config smoke passed\n";
