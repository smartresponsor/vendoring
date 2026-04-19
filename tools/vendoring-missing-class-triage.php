<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendoring missing class triage (structural).
 *
 * Reads missing-class-scan-v3 JSON and groups issues into actionable buckets:
 * - scanner-noise / namespace-only (should be near-zero with v3)
 * - missing Vendor entity references (candidate quarantine or entity restore)
 * - missing ServiceInterface refs (legacy path / stale dependency)
 * - cross-domain refs (Ledger / Observability)
 * - other
 *
 * Usage:
 *   php tools/vendoring-missing-class-triage.php
 *   php tools/vendoring-missing-class-triage.php --limit=2000
 */

$repoRoot = realpath(__DIR__ . '/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$args = $argv;
array_shift($args);
$limit = 2000;
foreach ($args as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $v = (int) substr($arg, 8);
        if ($v > 0) {
            $limit = $v;
        }
    }
}

$php = PHP_BINARY;
$scanScript = is_file($repoRoot . '/tools/vendoring-missing-class-scan-v3.php')
    ? 'tools/vendoring-missing-class-scan-v3.php'
    : (is_file($repoRoot . '/tools/vendoring-missing-class-scan-v2.php') ? 'tools/vendoring-missing-class-scan-v2.php' : 'tools/vendoring-missing-class-scan.php');

$cmd = [$php, $scanScript, '--json', '--limit=' . (string) $limit];
$descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$proc = proc_open($cmd, $descriptor, $pipes, $repoRoot);
if (!is_resource($proc)) {
    fwrite(STDERR, "ERROR: cannot run {$scanScript}\n");
    exit(2);
}
fclose($pipes[0]);
$json = stream_get_contents($pipes[1]);
fclose($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);
$exit = proc_close($proc);

if (!is_string($json) || '' === trim($json)) {
    fwrite(STDERR, "ERROR: empty JSON from {$scanScript}\n");
    if (is_string($stderr) && '' !== trim($stderr)) {
        fwrite(STDERR, $stderr . "\n");
    }
    exit(2);
}

/** @var array{issueList?:list<array<string,mixed>>,issueCount?:int,fileCount?:int} $payload */
$payload = json_decode($json, true);
if (!is_array($payload)) {
    fwrite(STDERR, "ERROR: invalid JSON from {$scanScript}\n");
    if (is_string($stderr) && '' !== trim($stderr)) {
        fwrite(STDERR, $stderr . "\n");
    }
    exit(2);
}

$issueList = $payload['issueList'] ?? [];
if (!is_array($issueList)) {
    $issueList = [];
}

$bucketMap = [
    'vendor-entity-missing' => [],
    'serviceinterface-missing' => [],
    'cross-domain-ledger-observability' => [],
    'namespace-noise' => [],
    'other' => [],
];

$fileHitMap = [];

foreach ($issueList as $row) {
    if (!is_array($row)) {
        continue;
    }
    $file = (string) ($row['file'] ?? '');
    $fqn = (string) ($row['fqn'] ?? '');
    $type = (string) ($row['type'] ?? '');

    if ('' !== $file) {
        $fileHitMap[$file] = ($fileHitMap[$file] ?? 0) + 1;
    }

    $bucket = 'other';
    if (preg_match('/^App\Vendoring\\\\Entity\\\\Vendor\\\\/', $fqn)) {
        $bucket = 'vendor-entity-missing';
    } elseif (preg_match('/^App\Vendoring\\\\ServiceInterface\\\\/', $fqn)) {
        $bucket = 'serviceinterface-missing';
    } elseif (preg_match('/^App\Vendoring\\\\(Service|DTO)\\\\(Ledger|Observability)\\\\/', $fqn)) {
        $bucket = 'cross-domain-ledger-observability';
    } elseif ('reference' === $type && preg_match('/^App\Vendoring\\\\[A-Z][A-Za-z0-9_]*$/', $fqn)) {
        $bucket = 'namespace-noise';
    }

    $bucketMap[$bucket][] = $row;
}

arsort($fileHitMap);

echo "Vendoring missing class triage\n";
echo "- Scanner: {$scanScript}\n";
echo '- File count: ' . (int) ($payload['fileCount'] ?? 0) . "\n";
echo '- Issue count (payload): ' . (int) ($payload['issueCount'] ?? count($issueList)) . "\n";
echo '- Issue count (loaded): ' . count($issueList) . "\n";

foreach ($bucketMap as $name => $rows) {
    echo "\n[{$name}] count=" . count($rows) . "\n";
    $show = array_slice($rows, 0, 12);
    foreach ($show as $r) {
        $f = (string) ($r['file'] ?? '');
        $q = (string) ($r['fqn'] ?? '');
        $t = strtoupper((string) ($r['type'] ?? ''));
        echo "  - {$t} {$f} :: {$q}\n";
    }
    if (count($rows) > count($show)) {
        echo '  ... (' . (count($rows) - count($show)) . " more)\n";
    }
}

echo "\nTop files by missing refs\n";
$rank = 0;
foreach ($fileHitMap as $file => $count) {
    $rank++;
    echo sprintf("%2d. %4d  %s\n", $rank, (int) $count, (string) $file);
    if ($rank >= 20) {
        break;
    }
}

echo "\nSuggested next structural action\n";
echo "- First: run v3 scanner + triage (this tool) to remove scanner noise from decision making.\n";
echo "- Then: quarantine or relocate entity-dependent legacy slices that reference missing App\Vendoring\\Entity\\Vendor\\* types.\n";
echo "- Then: normalize remaining imports file-by-file (PSR naming + path correctness) without formatter churn.\n";

exit(0);
