<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requiredFiles = [
    $root.'/README.md',
    $root.'/docs/release/RC_BASELINE.md',
    $root.'/docs/release/RC_ROADMAP.md',
    $root.'/docs/release/RC_CI_LANES.md',
    $root.'/docs/release/RC_RUNTIME_SURFACES.md',
    $root.'/docs/release/RC_DOCUMENTATION_SURFACES.md',
    $root.'/.github/workflows/quality.yml',
    $root.'/.github/workflows/runtime.yml',
    $root.'/.github/workflows/docs.yml',
    $root.'/.github/workflows/release-candidate.yml',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing RC file: '.$file.PHP_EOL);
        exit(1);
    }

    $contents = trim((string) file_get_contents($file));
    if ('' == $contents) {
        fwrite(STDERR, 'Empty RC file: '.$file.PHP_EOL);
        exit(1);
    }
}

echo "Release-candidate docs smoke OK\n";
