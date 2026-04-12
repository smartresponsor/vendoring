<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    $root . '/ops/policy/config/crm.yaml',
    $root . '/ops/policy/config/shadow.yaml',
    $root . '/ops/policy/config/api_v1_cors.yaml',
    $root . '/ops/policy/config/services_interface.yaml',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing file: {$file}
");
        exit(1);
    }

    $content = (string) file_get_contents($file);

    if (str_contains($content, 'example.com') || str_contains(strtolower($content), 'service example')) {
        fwrite(STDERR, "Example marker detected in: {$file}
");
        exit(1);
    }
}

echo 'no-example-config-smoke: ok
';
