<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$violations = [];

foreach ([
    '/.idea/Canonization.iml',
    '/.idea/Vendor.iml',
    '/.idea/workspace.xml',
    '/.ide/sr_default_inspector.xml',
] as $relative) {
    if (file_exists($root.$relative)) {
        $violations[] = ltrim($relative, '/');
    }
}

if (is_dir($root.'/.ide')) {
    $violations[] = '.ide';
}

if ([] !== $violations) {
    fwrite(STDERR, 'Forbidden IDE runtime artifacts remain: '.implode(', ', $violations).PHP_EOL);
    exit(1);
}

echo "idea-module-artifact smoke passed\n";
