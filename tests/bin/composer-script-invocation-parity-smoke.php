<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');

foreach ($scripts as $name => $commands) {
    if (!is_array($commands)) {
        continue;
    }

    foreach ($commands as $command) {
        if (!is_string($command) || !str_contains($command, 'vendor/bin/phpunit')) {
            continue;
        }

        if (!str_starts_with($command, 'php vendor/bin/phpunit ')) {
            fwrite(STDERR, 'Non-canonical phpunit invocation remains in script: ' . $name . PHP_EOL);
            exit(1);
        }
    }
}

$quality = $scripts['quality'] ?? [];
if (!is_array($quality)) {
    fwrite(STDERR, "quality script is missing or not an array\n");
    exit(1);
}

if ($quality !== array_values(array_unique($quality))) {
    fwrite(STDERR, "quality script contains duplicate entries\n");
    exit(1);
}

if (!isset($scripts['test:composer-script-invocation-parity'])) {
    fwrite(STDERR, "Missing test:composer-script-invocation-parity script\n");
    exit(1);
}

echo "composer-script-invocation-parity smoke passed\n";
