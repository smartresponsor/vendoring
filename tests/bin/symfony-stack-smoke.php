<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$checks = [
    'src/Kernel.php exists' => is_file($root . '/src/Kernel.php'),
    'config/bundles.php exists' => is_file($root . '/config/bundles.php'),
    'config/packages/framework.yaml exists' => is_file($root . '/config/packages/framework.yaml'),
    'config/packages/doctrine.yaml exists' => is_file($root . '/config/packages/doctrine.yaml'),
    'config/vendor_services.yaml exists' => is_file($root . '/config/vendor_services.yaml'),
    'config/vendor_routes.yaml exists' => is_file($root . '/config/vendor_routes.yaml'),
    'bin/console exists' => is_file($root . '/bin/console'),
    'public/index.php exists' => is_file($root . '/public/index.php'),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] ' . $label . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $label . PHP_EOL);
}

$bundles = require $root . '/config/bundles.php';
if (!is_array($bundles)) {
    fwrite(STDERR, '[FAIL] bundles.php must return array' . PHP_EOL);
    exit(1);
}

foreach ([
    'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
    'Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle',
] as $bundleClass) {
    if (!isset($bundles[$bundleClass])) {
        fwrite(STDERR, '[FAIL] missing bundle declaration: ' . $bundleClass . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] required bundles are declared' . PHP_EOL);
