<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);

$requiredFiles = [
    $root.'/bin/generate-rc-evidence.php',
    $root.'/docs/release/RC_EVIDENCE_PACK.md',
    $root.'/.github/workflows/release-candidate.yml',
    $root.'/.github/workflows/docs.yml',
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        fwrite(STDERR, 'Missing RC evidence file: '.$file.PHP_EOL);
        exit(1);
    }
}

$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');

foreach (['docs:rc-evidence', 'test:rc-evidence', 'quality:release-candidate'] as $script) {
    if (!array_key_exists($script, $scripts)) {
        fwrite(STDERR, 'Missing RC evidence composer script: '.$script.PHP_EOL);
        exit(1);
    }
}

echo "rc-evidence smoke passed\n";
