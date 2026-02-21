<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * CLI structure scanner for Vendoring.
 * Focus: code placement, PSR-4 path sanity, legacy/quarantine paths.
 * Not a formatter; no code rewriting.
 */

$repoRoot = realpath(__DIR__.'/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$srcRoot = $repoRoot.DIRECTORY_SEPARATOR.'src';
if (!is_dir($srcRoot)) {
    fwrite(STDERR, "ERROR: src/ not found at {$srcRoot}\n");
    exit(2);
}

$args = $argv;
array_shift($args);
$asJson = in_array('--json', $args, true);
$strict = in_array('--strict', $args, true);
$includeEmptyDir = in_array('--include-empty-dir', $args, true);

$forbiddenPrefixList = [
    'src/src/',
    'src/Interface/',
    'src/Adapter/',
];

$legacySegmentList = [
    'vendor-current',
    'vendor-bin',
    'seed',
];

$phpFileList = [];
$dirIssueList = [];
$dirWithFileSet = [];// relpath => true

$forbiddenFileList = [];
$legacyPathList = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $node) {
    $full = $node->getPathname();
    $rel = str_replace('\\', '/', substr($full, strlen($repoRoot) + 1));

    if ($node->isDir()) {
        $parts = array_values(array_filter(explode('/', $rel), static fn (string $v): bool => '' !== $v));
        for ($i = 1; $i < count($parts); $i++) {
            if ($parts[$i] === $parts[$i - 1]) {
                $dirIssueList[] = [
                    'type' => 'duplicate-segment',
                    'path' => $rel,
                    'segment' => $parts[$i],
                ];
                break;
            }
        }
        continue;
    }

    if (!$node->isFile()) {
        continue;
    }

    if (str_ends_with($rel, '.php')) {
        $phpFileList[] = $rel;

        $dirRel = dirname($rel);
        if ('.' !== $dirRel && '' !== $dirRel) {
            $dirRel = str_replace('\\', '/', $dirRel);
            $parts = array_values(array_filter(explode('/', $dirRel), static fn (string $v): bool => '' !== $v));
            $acc = [];
            foreach ($parts as $p) {
                $acc[] = $p;
                $dirWithFileSet[implode('/', $acc)] = true;
            }
        }

        foreach ($forbiddenPrefixList as $prefix) {
            if (str_starts_with($rel, $prefix)) {
                $forbiddenFileList[] = [
                    'type' => 'forbidden-prefix',
                    'path' => $rel,
                    'prefix' => $prefix,
                ];
                break;
            }
        }

        foreach ($legacySegmentList as $seg) {
            if (false !== strpos($rel, '/'.$seg.'/')) {
                $legacyPathList[] = [
                    'type' => 'legacy-segment',
                    'path' => $rel,
                    'segment' => $seg,
                ];
                break;
            }
        }
    }
}

if (!$includeEmptyDir) {
    $dirIssueList = array_values(array_filter($dirIssueList, static fn (array $hit): bool => isset($dirWithFileSet[$hit['path']])));
}

$result = [
    'repoRoot' => $repoRoot,
    'phpFileCount' => count($phpFileList),
    'forbiddenFile' => $forbiddenFileList,
    'dirIssue' => $dirIssueList,
    'legacyPath' => $legacyPathList,
];

if ($asJson) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
} else {
    echo "Vendoring structure scan\n";
    echo "- PHP files under src/: ".count($phpFileList)."\n";

    echo "- Forbidden placement hits: ".count($forbiddenFileList)."\n";
    foreach ($forbiddenFileList as $hit) {
        echo "  * {$hit['path']} (prefix={$hit['prefix']})\n";
    }

    echo "- Duplicate-segment directories: ".count($dirIssueList).($includeEmptyDir ? " (incl. empty)" : " (non-empty only)")."\n";
    foreach ($dirIssueList as $hit) {
        echo "  * {$hit['path']} (segment={$hit['segment']})\n";
    }

    echo "- Legacy-path hits: ".count($legacyPathList)."\n";
    foreach ($legacyPathList as $hit) {
        echo "  * {$hit['path']} (segment={$hit['segment']})\n";
    }

    if ($strict) {
        echo "- Mode: strict\n";
    }
}

$hasProblem = 0 !== count($forbiddenFileList) || 0 !== count($dirIssueList) || 0 !== count($legacyPathList);

if ($strict && $hasProblem) {
    exit(3);
}

exit(0);
