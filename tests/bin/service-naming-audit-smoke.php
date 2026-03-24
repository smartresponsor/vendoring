#!/usr/bin/env php
<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$command = sprintf('php %s', escapeshellarg($root . '/tools/vendoring-service-naming-audit.php'));
exec($command, $output, $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "service naming audit command failed\n");
    exit(1);
}

$payload = json_decode(implode("\n", $output), true);
if (!is_array($payload)) {
    fwrite(STDERR, "service naming audit returned invalid json\n");
    exit(1);
}

if (!array_key_exists('violations', $payload) || !is_array($payload['violations'])) {
    fwrite(STDERR, "service naming audit payload has no violations key\n");
    exit(1);
}

echo sprintf("service naming audit completed with %d suggestion(s)\n", count($payload['violations']));
