<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$activeDocs = [
    'README.md',
    'docs/internal/DOCUMENTATION_CANON.md',
    'docs/internal/LAYER3_STRUCTURE_NAMING_CANON.md',
    'docs/modules/ROOT/pages/install.adoc',
    'docs/release/RC_RUNTIME_SURFACES.md',
    'docs/GITHUB_ISSUES_ARCHITECTURE_REVIEW_V2.md',
    'docs/zero-controller-runtime-contract.md',
];

$forbidden = [
    'config/vendor_routes.yaml',
    'App\\Vendoring\\Controller\\',
    'Controller\\Vendor',
    'surface-builder',
    'surface builder',
    'HTTP/surface-builder',
];

$errors = [];

foreach ($activeDocs as $relativePath) {
    $path = $root.'/'.$relativePath;
    if (!is_file($path)) {
        $errors[] = 'Missing active documentation file: '.$relativePath;
        continue;
    }

    $contents = (string) file_get_contents($path);
    foreach ($forbidden as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = sprintf('%s contains retired controller-era vocabulary: %s', $relativePath, $needle);
        }
    }
}

if ([] !== $errors) {
    fwrite(STDERR, 'Vendoring active documentation zero-controller smoke failed:
');
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error.'
');
    }
    exit(1);
}

echo 'Vendoring active documentation zero-controller smoke passed.
';
