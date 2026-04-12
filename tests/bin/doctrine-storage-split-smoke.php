<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$doctrine = (string) file_get_contents($root . '/config/packages/doctrine.yaml');
$vendorBridge = (string) file_get_contents($root . '/config/packages/vendor_bridge.yaml');

$checks = [
    ['default connection must target user_data', str_contains($doctrine, 'default_connection: user_data')],
    ['user_data connection must be present', str_contains($doctrine, 'user_data:')],
    ['app_data connection must be present', str_contains($doctrine, 'app_data:')],
    ['user_data must use VENDOR_DSN', str_contains($doctrine, 'VENDOR_DSN')],
    ['app_data must use VENDOR_SQLITE_DSN', str_contains($doctrine, 'VENDOR_SQLITE_DSN')],
    ['ORM must bind to user_data', str_contains($doctrine, 'connection: user_data')],
    ['vendor bridge must define vendor.sqlite_dsn', str_contains($vendorBridge, 'vendor.sqlite_dsn:')],
];

$failed = false;
foreach ($checks as [$label, $ok]) {
    if (!$ok) {
        fwrite(STDERR, sprintf("[FAIL] %s\n", $label));
        $failed = true;
    }
}

if ($failed) {
    exit(1);
}

fwrite(STDOUT, "doctrine storage split smoke passed\n");
