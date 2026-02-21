<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendoring structural quality gate (v3).
 * Uses missing-class-scan-v3 when available.
 */

$repoRoot = realpath(__DIR__ . '/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$args = $argv;
array_shift($args);
$limitArg = '--limit=500';
foreach ($args as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limitArg = $arg;
        break;
    }
}

$php = PHP_BINARY;
$missingScan = 'tools/vendoring-missing-class-scan-v3.php';
if (!is_file($repoRoot . '/' . $missingScan)) {
    $missingScan = is_file($repoRoot . '/tools/vendoring-missing-class-scan-v2.php')
        ? 'tools/vendoring-missing-class-scan-v2.php'
        : 'tools/vendoring-missing-class-scan.php';
}

$cmdList = [
    ['tools/vendoring-structure-scan.php', '--strict'],
    ['tools/vendoring-psr4-scan.php', '--strict'],
    [$missingScan, '--strict', $limitArg],
];

$hasFail = false;

echo "Vendoring quality gate (v3)\n";
echo "- Missing scan: {$missingScan}\n";
echo "- Missing limit: {$limitArg}\n";

foreach ($cmdList as $cmd) {
    echo "\n> " . implode(' ', $cmd) . "\n";
    $proc = proc_open(
        array_merge([$php], $cmd),
        [0 => STDIN, 1 => STDOUT, 2 => STDERR],
        $pipes,
        $repoRoot
    );

    if (!is_resource($proc)) {
        fwrite(STDERR, "ERROR: cannot run {$cmd[0]}\n");
        $hasFail = true;
        continue;
    }

    $code = proc_close($proc);
    if (!is_int($code) || 0 !== $code) {
        echo "[FAIL] exit={$code}\n";
        $hasFail = true;
    } else {
        echo "[OK]\n";
    }
}

if ($hasFail) {
    echo "\nQUALITY GATE: FAIL\n";
    exit(1);
}

echo "\nQUALITY GATE: PASS\n";
exit(0);
