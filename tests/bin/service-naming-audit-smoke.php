#!/usr/bin/env php
<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$command = sprintf('php %s', escapeshellarg($root.'/tools/vendoring-service-naming-audit.php'));
exec($command, $output, $exitCode);

if (0 !== $exitCode) {
    fwrite(STDERR, "service naming audit command failed\n");
    exit(1);
}

$payload = vendoring_decode_json_array(implode("\n", $output));
if ([] === $payload) {
    fwrite(STDERR, "service naming audit returned invalid json\n");
    exit(1);
}

/** @var array<string, mixed> $payload */
if (!array_key_exists('violations', $payload) || !is_array($payload['violations'])) {
    fwrite(STDERR, "service naming audit payload has no violations key\n");
    exit(1);
}

echo sprintf("service naming audit completed with %d suggestion(s)\n", count($payload['violations']));
