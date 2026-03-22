<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$file = $root.'/ops/policy/config/services_interface.yaml';

if (!is_file($file)) {
    fwrite(STDERR, "Missing file: {$file}\n");
    exit(1);
}

$content = (string) file_get_contents($file);

$required = [
    'App\\:',
    'App\\ServiceInterface\\Order\\OrderPaymentInterface:',
    'alias: App\\Service\\Order\\OrderPaymentService',
];

foreach ($required as $needle) {
    if (!str_contains($content, $needle)) {
        fwrite(STDERR, "Missing canonical App namespace marker: {$needle}\n");
        exit(1);
    }
}

$forbidden = [
    'Vendor\\:',
    'Vendor\\ServiceInterface\\Order\\OrderPaymentInterface:',
    'alias: Vendor\\Service\\Order\\OrderPaymentService',
];

foreach ($forbidden as $needle) {
    if (str_contains($content, $needle)) {
        fwrite(STDERR, "Legacy Vendor namespace marker detected: {$needle}\n");
        exit(1);
    }
}

echo "app-namespace-repository-smoke: ok\n";
