<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendor canon guard (fast scan).
 * - detects obvious path segment repeats (e.g. src/Controller/Controller/Controller)
 * - detects namespace/path mismatch for App\Vendoring\\* classes under src/
 *
 * Usage:
 *   php tools/canon/vendor-scan.php
 */

$root = dirname(__DIR__, 2);
$src = $root . '/src';
if (!is_dir($src)) {
    fwrite(STDERR, "src/ not found\n");
    exit(2);
}

$issues = [];

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
);

foreach ($rii as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());

    // repeat segments like /Controller/Controller/
    $segments = explode('/', trim(str_replace($src, '', $path), '/'));
    for ($i = 1; $i < count($segments); $i++) {
        if ($segments[$i] !== '' && $segments[$i] === $segments[$i - 1]) {
            $issues[] = "repeat_segment\t{$path}\t{$segments[$i - 1]}/{$segments[$i]}";
            break;
        }
    }

    if (str_ends_with($path, '.php') !== true) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        $issues[] = "read_fail\t{$path}";
        continue;
    }

    // namespace extraction (best-effort)
    if (!preg_match('/^\s*namespace\s+([^;]+);/m', $content, $m)) {
        continue;
    }
    $ns = trim($m[1]);
    if (!str_starts_with($ns, 'App\Vendoring\\')) {
        continue;
    }

    // expected PSR-4 relative path
    $rel = trim(str_replace($src . '/', '', $path), '/');
    $relNoExt = preg_replace('/\.php$/', '', $rel);
    $expected = 'App\Vendoring\\' . str_replace('/', '\\', $relNoExt);

    // allow files that declare multiple classes (rare) - just compare namespace+basename class pattern
    $base = basename($relNoExt);
    if (preg_match('/\b(class|interface|trait)\s+' . preg_quote($base, '/') . '\b/', $content) !== 1) {
        continue;
    }

    $declared = $ns . '\\' . $base;
    if ($declared !== $expected) {
        $issues[] = "ns_path_mismatch\t{$path}\tdeclared={$declared}\texpected={$expected}";
    }
}

if ($issues !== []) {
    foreach ($issues as $line) {
        echo $line, "\n";
    }
    fwrite(STDERR, 'Vendor canon scan: FAIL (' . count($issues) . " issue(s))\n");
    exit(1);
}

echo "Vendor canon scan: OK\n";
