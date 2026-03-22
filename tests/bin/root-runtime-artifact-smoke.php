<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cache = $root.'/var/.php-cs-fixer.cache';
$actionsLog = $root.'/.commanding/logs/actions.log';

if (is_file($cache)) {
    fwrite(STDERR, "Persistent php-cs-fixer cache must not exist in cumulative source snapshot\n");
    exit(1);
}

if (is_file($actionsLog)) {
    fwrite(STDERR, "Committed operational action log must not exist in cumulative source snapshot\n");
    exit(1);
}

fwrite(STDOUT, "[OK] root runtime cache artifacts are absent\n");
