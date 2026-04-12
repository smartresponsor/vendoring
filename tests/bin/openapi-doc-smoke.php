<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/bin/generate-openapi.php');
passthru($cmd, $code);
if (0 !== $code) {
    fwrite(STDERR, "OpenAPI generation failed.\n");
    exit($code);
}

foreach (['build/docs/openapi.json', 'build/docs/openapi.yaml'] as $path) {
    if (!is_file($root . '/' . $path)) {
        fwrite(STDERR, sprintf("Missing generated OpenAPI artifact: %s\n", $path));
        exit(1);
    }
}

echo "OpenAPI docs smoke OK\n";
