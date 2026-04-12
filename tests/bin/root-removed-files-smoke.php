<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$target = $root . '/REMOVED_FILES.txt';

if (is_file($target)) {
    fwrite(STDERR, "REMOVED_FILES.txt must not exist in repository root\n");
    exit(1);
}

fwrite(STDOUT, "root removed-files smoke: ok\n");
