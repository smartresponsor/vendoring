<?php

declare(strict_types=1);

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

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];

foreach (['docs:rc-evidence', 'test:rc-evidence', 'quality:release-candidate'] as $script) {
    if (!array_key_exists($script, $scripts)) {
        fwrite(STDERR, 'Missing RC evidence composer script: '.$script.PHP_EOL);
        exit(1);
    }
}

echo "rc-evidence smoke passed\n";
