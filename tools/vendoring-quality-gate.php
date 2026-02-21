<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendoring structural quality gate.
 *
 * Runs report-only scanners in strict mode and fails on structural violations.
 *
 * Usage:
 *   php tools/vendoring-quality-gate.php
 */

$repoRoot = realpath(__DIR__.'/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$php = PHP_BINARY;
$cmdList = [
    ['tools/vendoring-structure-scan.php', '--strict'],
    ['tools/vendoring-psr4-scan.php', '--strict'],
    ['tools/vendoring-missing-class-scan.php', '--strict', '--limit=200'],
];

$hasFail = false;

foreach ($cmdList as $cmd) {
    $full = array_merge([$php], $cmd);

    $proc = proc_open(
        $full,
        [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ],
        $pipes,
        $repoRoot
    );

    if (!is_resource($proc)) {
        fwrite(STDERR, "ERROR: cannot run ".$cmd[0]."\n");
        $hasFail = true;
        continue;
    }

    $code = proc_close($proc);
    if (!is_int($code) || 0 !== $code) {
        $hasFail = true;
    }
}

exit($hasFail ? 1 : 0);
