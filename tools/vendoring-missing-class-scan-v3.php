<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Vendoring missing class scan (v3).
 *
 * Focus:
 * - structure / namespace / import placement
 * - detect unresolved App\\* imports and FQCN references
 * - reduce false positives (group use, namespace aliases, strings/comments)
 *
 * Usage:
 *   php tools/vendoring-missing-class-scan-v3.php
 *   php tools/vendoring-missing-class-scan-v3.php --strict --limit=1000
 *   php tools/vendoring-missing-class-scan-v3.php --json > report/vendoring-missing-class-scan-v3.json
 */

$repoRoot = realpath(__DIR__ . '/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$args = $argv;
array_shift($args);

$asJson = in_array('--json', $args, true);
$strict = in_array('--strict', $args, true);
$limit = 200;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $v = (int) substr($arg, 8);
        if ($v > 0) {
            $limit = $v;
        }
    }
}

$srcRoot = $repoRoot . DIRECTORY_SEPARATOR . 'src';
if (!is_dir($srcRoot)) {
    fwrite(STDERR, "ERROR: src/ not found\n");
    exit(2);
}

function normalizeOpenTagV3(string $code): string
{
    // token_get_all treats "<?phpdeclare" as inline HTML.
    if (str_starts_with($code, '<?php') && isset($code[5]) && !ctype_space($code[5])) {
        return "<?php\n" . substr($code, 5);
    }

    return $code;
}

/** @return list<string> */
function splitTopLevelCommaV3(string $s): array
{
    $out = [];
    $buf = '';
    $braceDepth = 0;
    $len = strlen($s);

    for ($i = 0; $i < $len; $i++) {
        $ch = $s[$i];
        if ('{' === $ch) {
            $braceDepth++;
            $buf .= $ch;
            continue;
        }
        if ('}' === $ch) {
            if ($braceDepth > 0) {
                $braceDepth--;
            }
            $buf .= $ch;
            continue;
        }
        if (',' === $ch && 0 === $braceDepth) {
            $part = trim($buf);
            if ('' !== $part) {
                $out[] = $part;
            }
            $buf = '';
            continue;
        }
        $buf .= $ch;
    }

    $tail = trim($buf);
    if ('' !== $tail) {
        $out[] = $tail;
    }

    return $out;
}

/**
 * Expand a single class import expression (no leading "use", no trailing semicolon).
 * Supports:
 * - Foo\\Bar
 * - Foo\\Bar as Baz
 * - Foo\\{Bar, Baz as Qux}
 *
 * @return list<string>
 */
function expandUseExprV3(string $expr): array
{
    $expr = trim($expr);
    if ('' === $expr) {
        return [];
    }

    if (preg_match('/^(function|const)\s+/i', $expr)) {
        return [];
    }

    $expr = preg_replace('/\s+/', ' ', $expr) ?? $expr;

    if (str_contains($expr, '{') && str_contains($expr, '}')) {
        $l = strpos($expr, '{');
        $r = strrpos($expr, '}');
        if (false === $l || false === $r || $r <= $l) {
            return [];
        }

        $prefix = rtrim(trim(substr($expr, 0, $l)), '\\');
        $inner = substr($expr, $l + 1, $r - $l - 1);
        $parts = splitTopLevelCommaV3($inner);
        $out = [];

        foreach ($parts as $part) {
            if (preg_match('/^(.+?)\s+as\s+[A-Za-z_][A-Za-z0-9_]*$/i', $part, $m)) {
                $part = trim($m[1]);
            }
            $part = preg_replace('/\s+/', '', $part) ?? $part;
            $part = ltrim($part, '\\');
            if ('' === $part) {
                continue;
            }
            $out[] = ltrim($prefix . '\\' . $part, '\\');
        }

        return $out;
    }

    if (preg_match('/^(.+?)\s+as\s+[A-Za-z_][A-Za-z0-9_]*$/i', $expr, $m)) {
        $expr = trim($m[1]);
    }

    $expr = preg_replace('/\s+/', '', $expr) ?? $expr;
    $expr = ltrim($expr, '\\');

    return '' === $expr ? [] : [$expr];
}

/** @return array{namespace:?string,typeList:list<string>,importList:list<string>,scanText:string} */
function parsePhpMetaV3(string $code): array
{
    $code = normalizeOpenTagV3($code);
    $tokens = token_get_all($code);

    $namespace = null;
    $typeList = [];
    $importList = [];
    $scanText = '';

    $collectNamespace = false;
    $seenFirstType = false;
    $n = count($tokens);

    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        $id = is_array($t) ? $t[0] : null;
        $text = is_array($t) ? $t[1] : $t;

        // Build code-only scan text (exclude comments/strings/docblocks).
        if (is_array($t)) {
            if (in_array($id, [T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
                $scanText .= ' ';
            } else {
                $scanText .= $text;
            }
        } else {
            $scanText .= $text;
        }

        if (is_array($t) && T_NAMESPACE === $id) {
            $nsBuf = '';
            for ($j = $i + 1; $j < $n; $j++) {
                $tt = $tokens[$j];
                if (!is_array($tt) && (';' === $tt || '{' === $tt)) {
                    break;
                }
                if (is_array($tt)) {
                    $tokId = $tt[0];
                    $isNsToken = in_array($tokId, [T_STRING, T_NS_SEPARATOR], true)
                        || (defined('T_NAME_QUALIFIED') && T_NAME_QUALIFIED === $tokId)
                        || (defined('T_NAME_FULLY_QUALIFIED') && T_NAME_FULLY_QUALIFIED === $tokId)
                        || (defined('T_NAME_RELATIVE') && T_NAME_RELATIVE === $tokId);
                    if ($isNsToken) {
                        $nsBuf .= $tt[1];
                    }
                }
            }
            $namespace = '' !== $nsBuf ? ltrim($nsBuf, '\\') : null;
            continue;
        }

        if (is_array($t) && T_USE === $id && !$seenFirstType) {
            $expr = '';
            for ($j = $i + 1; $j < $n; $j++) {
                $tt = $tokens[$j];
                if (!is_array($tt) && ';' === $tt) {
                    break;
                }
                $expr .= is_array($tt) ? $tt[1] : $tt;
            }
            foreach (splitTopLevelCommaV3($expr) as $chunk) {
                foreach (expandUseExprV3($chunk) as $fqcn) {
                    $importList[] = $fqcn;
                }
            }
            continue;
        }

        if (is_array($t) && in_array($id, [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
            // Ignore anonymous class.
            $prevNonWs = null;
            for ($j = $i - 1; $j >= 0; $j--) {
                $pt = $tokens[$j];
                if (is_array($pt) && T_WHITESPACE === $pt[0]) {
                    continue;
                }
                $prevNonWs = $pt;
                break;
            }
            if ($id === T_CLASS && is_array($prevNonWs) && T_NEW === $prevNonWs[0]) {
                $seenFirstType = true;
                continue;
            }

            $seenFirstType = true;
            for ($j = $i + 1; $j < $n; $j++) {
                $tt = $tokens[$j];
                if (is_array($tt) && T_STRING === $tt[0]) {
                    $name = $tt[1];
                    $typeList[] = (null !== $namespace && '' !== $namespace) ? $namespace . '\\' . $name : $name;
                    break;
                }
            }
            continue;
        }

        if (is_array($t) && function_exists('defined') && defined('T_ENUM') && constant('T_ENUM') === $id) {
            $seenFirstType = true;
            for ($j = $i + 1; $j < $n; $j++) {
                $tt = $tokens[$j];
                if (is_array($tt) && T_STRING === $tt[0]) {
                    $name = $tt[1];
                    $typeList[] = (null !== $namespace && '' !== $namespace) ? $namespace . '\\' . $name : $name;
                    break;
                }
            }
            continue;
        }
    }

    $typeList = array_values(array_unique(array_map(static fn(string $v): string => ltrim($v, '\\'), $typeList)));
    $importList = array_values(array_unique(array_map(static fn(string $v): string => ltrim($v, '\\'), $importList)));

    return [
        'namespace' => $namespace,
        'typeList' => $typeList,
        'importList' => $importList,
        'scanText' => $scanText,
    ];
}

/** @return list<string> */
function findPhpFileListV3(string $root): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    $list = [];
    foreach ($rii as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }
        if (!$file->isFile()) {
            continue;
        }
        if ('php' !== strtolower((string) $file->getExtension())) {
            continue;
        }
        $list[] = $file->getPathname();
    }

    sort($list);
    return $list;
}

/** @return list<string> */
function extractAppRefsV3(string $codeOnlyText): array
{
    $matchList = [];
    preg_match_all('~(?:^|[^A-Za-z0-9_])((?:\\\\)?App\\\\[A-Za-z_][A-Za-z0-9_\\\\]*)~', $codeOnlyText, $m);
    foreach (($m[1] ?? []) as $raw) {
        $fqn = ltrim(trim((string) $raw), '\\');
        if ('App' === $fqn || str_ends_with($fqn, '\\')) {
            continue;
        }
        $matchList[] = $fqn;
    }

    $matchList = array_values(array_unique($matchList));
    sort($matchList);
    return $matchList;
}

/** @param array<string,true> $knownNamespaceSet */
function isNamespacePrefixV3(string $fqn, array $knownNamespaceSet): bool
{
    $p = $fqn;
    while (false !== ($pos = strrpos($p, '\\'))) {
        $p = substr($p, 0, $pos);
        if (isset($knownNamespaceSet[$p])) {
            return true;
        }
    }

    return false;
}

/** @return array{0:array<string,true>,1:array<string,true>,2:array<string,array{namespace:?string,importList:list<string>,scanText:string}>} */
function buildIndexV3(array $phpFileList, string $srcRoot): array
{
    $knownTypeSet = [];
    $knownNamespaceSet = [];
    $fileMetaMap = [];

    foreach ($phpFileList as $file) {
        $code = @file_get_contents($file);
        if (!is_string($code)) {
            continue;
        }
        $meta = parsePhpMetaV3($code);
        $rel = str_replace('\\', '/', substr($file, strlen($srcRoot) + 1));
        $fileMetaMap[$rel] = [
            'namespace' => $meta['namespace'],
            'importList' => $meta['importList'],
            'scanText' => $meta['scanText'],
        ];
        foreach ($meta['typeList'] as $type) {
            $knownTypeSet[$type] = true;
            $parts = explode('\\', $type);
            array_pop($parts);
            $ns = '';
            foreach ($parts as $part) {
                $ns = '' === $ns ? $part : ($ns . '\\' . $part);
                $knownNamespaceSet[$ns] = true;
            }
        }
    }

    return [$knownTypeSet, $knownNamespaceSet, $fileMetaMap];
}

$fileList = findPhpFileListV3($srcRoot);
[$knownTypeSet, $knownNamespaceSet, $fileMetaMap] = buildIndexV3($fileList, $srcRoot);

$issueList = [];
$seenIssueKeySet = [];

$pushIssue = static function (array $issue) use (&$issueList, &$seenIssueKeySet): void {
    $type = (string) ($issue['type'] ?? 'unknown');
    $file = (string) ($issue['file'] ?? '');
    $fqn = (string) ($issue['fqn'] ?? '');
    $msg = (string) ($issue['message'] ?? '');
    $key = $type . '|' . $file . '|' . $fqn . '|' . $msg;
    if (isset($seenIssueKeySet[$key])) {
        return;
    }
    $seenIssueKeySet[$key] = true;
    $issueList[] = $issue;
};

foreach ($fileMetaMap as $rel => $meta) {
    foreach (($meta['importList'] ?? []) as $fqn) {
        if (!is_string($fqn) || !str_starts_with($fqn, 'App\\')) {
            continue;
        }
        if (isset($knownTypeSet[$fqn])) {
            continue;
        }
        if (isNamespacePrefixV3($fqn, $knownNamespaceSet)) {
            continue;
        }
        $pushIssue(['type' => 'import', 'file' => $rel, 'fqn' => $fqn]);
    }

    foreach (extractAppRefsV3((string) ($meta['scanText'] ?? '')) as $fqn) {
        if (isset($knownTypeSet[$fqn])) {
            continue;
        }
        if (isNamespacePrefixV3($fqn, $knownNamespaceSet)) {
            continue;
        }
        $fileNs = $meta['namespace'] ?? null;
        if (is_string($fileNs) && '' !== $fileNs && $fqn === $fileNs) {
            continue;
        }
        $pushIssue(['type' => 'reference', 'file' => $rel, 'fqn' => $fqn]);
    }
}

usort(
    $issueList,
    static function (array $a, array $b): int {
        $ak = (string) (($a['type'] ?? '') . '|' . ($a['file'] ?? '') . '|' . ($a['fqn'] ?? '') . '|' . ($a['message'] ?? ''));
        $bk = (string) (($b['type'] ?? '') . '|' . ($b['file'] ?? '') . '|' . ($b['fqn'] ?? '') . '|' . ($b['message'] ?? ''));
        return strcmp($ak, $bk);
    }
);

if ($asJson) {
    $out = [
        'fileCount' => count($fileList),
        'knownTypeCount' => count($knownTypeSet),
        'knownNamespaceCount' => count($knownNamespaceSet),
        'issueCount' => count($issueList),
        'issueList' => $limit > 0 ? array_slice($issueList, 0, $limit) : $issueList,
    ];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(($strict && count($issueList) > 0) ? 1 : 0);
}

echo "Vendoring missing class scan (v3)\n";
echo "- PHP files under src/: " . count($fileList) . "\n";
echo "- Known types: " . count($knownTypeSet) . "\n";
echo "- Known namespaces: " . count($knownNamespaceSet) . "\n";
echo "- Issue count: " . count($issueList) . "\n";

$shown = 0;
foreach ($issueList as $row) {
    if ($shown >= $limit) {
        echo "  ... (limit reached)\n";
        break;
    }
    $tag = strtoupper((string) ($row['type'] ?? 'ISSUE'));
    $file = (string) ($row['file'] ?? '');
    $fqn = (string) ($row['fqn'] ?? '');
    echo "  * [{$tag}] {$file} {$fqn}\n";
    $shown++;
}

exit(($strict && count($issueList) > 0) ? 1 : 0);
