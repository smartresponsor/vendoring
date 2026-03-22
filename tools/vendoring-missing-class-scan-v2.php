<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * Missing class scan for Vendoring (v2, noise-reduced).
 *
 * Goal:
 * - Detect App\* imports and App\* FQCN references that do not resolve to a known type in repo.
 *
 * Focus:
 * - Structure / namespace / imports / legacy references.
 * - No formatting changes, no auto-fix.
 *
 * Improvements vs earlier scanner:
 * - Handles group-use imports: use Foo\Bar\{Baz, Qux as Alias};
 * - Ignores "use function" and "use const" imports.
 * - Ignores namespace-only tokens (e.g. App\Command\Vendor) when they are known namespace prefixes.
 * - Ignores trailing namespace fragments ending with "\\".
 * - Avoids most string/comment/docblock false positives by scanning tokenized non-string/non-comment code.
 * - Dedupe output entries for stable reports.
 *
 * Usage:
 *   php tools/vendoring-missing-class-scan-v2.php
 *   php tools/vendoring-missing-class-scan-v2.php --limit=2000 --strict
 *   php tools/vendoring-missing-class-scan-v2.php --json > report/vendoring-missing-class-scan-v2.json
 */

$repoRoot = realpath(__DIR__.'/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$args = $argv;
array_shift($args);

$asJson = in_array('--json', $args, true);
$strict = in_array('--strict', $args, true);

$limit = 200;
foreach ($args as $a) {
    if (str_starts_with($a, '--limit=')) {
        $v = (int) substr($a, strlen('--limit='));
        if ($v > 0) {
            $limit = $v;
        }
    }
}

$srcRoot = $repoRoot.DIRECTORY_SEPARATOR.'src';
if (!is_dir($srcRoot)) {
    fwrite(STDERR, "ERROR: src/ not found\n");
    exit(2);
}

/**
 * token_get_all() treats "<?phpdeclare" as inline HTML.
 */
function normalizeOpenTag(string $code): string
{
    if (str_starts_with($code, "<?php") && isset($code[5]) && !ctype_space($code[5])) {
        return "<?php\n".substr($code, 5);
    }

    return $code;
}

/**
 * Split by commas at top level (outside braces).
 *
 * @return list<string>
 */
function splitTopLevelComma(string $s): array
{
    $out = [];
    $buf = '';
    $depthBrace = 0;
    $len = strlen($s);

    for ($i = 0; $i < $len; $i++) {
        $ch = $s[$i];

        if ('{' === $ch) {
            $depthBrace++;
            $buf .= $ch;
            continue;
        }

        if ('}' === $ch) {
            if ($depthBrace > 0) {
                $depthBrace--;
            }
            $buf .= $ch;
            continue;
        }

        if (',' === $ch && 0 === $depthBrace) {
            $part = trim($buf);
            if ('' !== $part) {
                $out[] = $part;
            }
            $buf = '';
            continue;
        }

        $buf .= $ch;
    }

    $part = trim($buf);
    if ('' !== $part) {
        $out[] = $part;
    }

    return $out;
}

/**
 * Expand a class import expression (already stripped from "use ").
 *
 * Accepted forms:
 *   App\Foo\Bar
 *   App\Foo\Bar as Baz
 *   App\Foo\{Bar,Baz as Qux}
 *
 * @return list<string> canonical FQCN imports (without leading slash)
 */
function expandClassImportExpression(string $expr): array
{
    $expr = trim($expr);
    if ('' === $expr) {
        return [];
    }

    // Skip function/const imports completely (scanner is class-focused).
    if (preg_match('/^(function|const)\s+/i', $expr)) {
        return [];
    }

    // Normalize spaces for simpler parsing.
    $expr = preg_replace('/\s+/', ' ', $expr) ?? $expr;

    // Group use: Prefix\{A, B as C}
    if (str_contains($expr, '{') && str_contains($expr, '}')) {
        $l = strpos($expr, '{');
        $r = strrpos($expr, '}');
        if (false === $l || false === $r || $r <= $l) {
            return [];
        }

        $prefix = rtrim(trim(substr($expr, 0, $l)), '\\');
        $inner = substr($expr, $l + 1, $r - $l - 1);
        $chunkList = splitTopLevelComma($inner);

        $out = [];
        foreach ($chunkList as $chunk) {
            $chunk = trim($chunk);
            if ('' === $chunk) {
                continue;
            }

            if (preg_match('/^(.+?)\s+as\s+[A-Za-z_][A-Za-z0-9_]*$/i', $chunk, $m)) {
                $chunk = trim($m[1]);
            }

            $chunk = preg_replace('/\s+/', '', $chunk) ?? $chunk;
            $chunk = ltrim($chunk, '\\');

            if ('' === $chunk) {
                continue;
            }

            $out[] = ltrim($prefix.'\\'.$chunk, '\\');
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

/**
 * @return array{
 *   namespace: string|null,
 *   typeList: list<array{kind:string,name:string}>,
 *   importList: list<string>,
 *   scanText: string
 * }
 */
function parsePhpForMissingScan(string $code): array
{
    $code = normalizeOpenTag($code);
    $tokens = token_get_all($code);

    $ns = null;
    $typeList = [];
    $importList = [];
    $scanText = '';

    $n = count($tokens);
    $prevSignificant = null;
    $firstTypeSeen = false;

    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];

        if (is_array($t)) {
            $id = $t[0];
            $text = $t[1];

            // Build code-only scan text (skip comments and string literals to reduce noise).
            $skipForScan = [
                T_COMMENT,
                T_DOC_COMMENT,
                T_CONSTANT_ENCAPSED_STRING,
                T_ENCAPSED_AND_WHITESPACE,
            ];
            if (defined('T_INLINE_HTML') && T_INLINE_HTML === $id) {
                $skipForScan[] = T_INLINE_HTML;
            }
            if (!in_array($id, $skipForScan, true)) {
                $scanText .= $text;
            }

            if (T_NAMESPACE === $id) {
                $nsParts = [];
                for ($j = $i + 1; $j < $n; $j++) {
                    $tj = $tokens[$j];
                    if (is_array($tj) && (T_STRING === $tj[0] || (defined('T_NAME_QUALIFIED') && T_NAME_QUALIFIED === $tj[0]) || T_NS_SEPARATOR === $tj[0])) {
                        $nsParts[] = $tj[1];
                        continue;
                    }
                    if (';' === $tj || '{' === $tj) {
                        break;
                    }
                }
                $ns = trim(str_replace('\\\\', '\\', implode('', $nsParts)));
            }

            if (!$firstTypeSeen && T_USE === $id) {
                // Ignore closure use: preceded by ')' or followed by '('.
                $nextNonWs = null;
                for ($j = $i + 1; $j < $n; $j++) {
                    $tj = $tokens[$j];
                    if (is_array($tj) && T_WHITESPACE === $tj[0]) {
                        continue;
                    }
                    $nextNonWs = $tj;
                    break;
                }

                if (')' !== $prevSignificant && '(' !== $nextNonWs) {
                    $chunk = '';
                    for ($j = $i + 1; $j < $n; $j++) {
                        $tj = $tokens[$j];
                        if (';' === $tj) {
                            break;
                        }
                        $chunk .= is_array($tj) ? $tj[1] : $tj;
                    }

                    foreach (splitTopLevelComma(trim($chunk)) as $useExpr) {
                        foreach (expandClassImportExpression($useExpr) as $fqn) {
                            if ('' !== $fqn) {
                                $importList[] = ltrim($fqn, '\\');
                            }
                        }
                    }
                }
            }

            if (T_CLASS === $id || T_INTERFACE === $id || T_TRAIT === $id || (defined('T_ENUM') && T_ENUM === $id)) {
                // Ignore ::class and anonymous class.
                if (T_DOUBLE_COLON === $prevSignificant || T_NEW === $prevSignificant) {
                    continue;
                }

                $kind = match ($id) {
                    T_CLASS => 'class',
                    T_INTERFACE => 'interface',
                    T_TRAIT => 'trait',
                    default => 'enum',
                };

                $name = null;
                for ($j = $i + 1; $j < $n; $j++) {
                    $tj = $tokens[$j];
                    if (is_array($tj) && T_STRING === $tj[0]) {
                        $name = $tj[1];
                        break;
                    }
                    if (is_string($tj) && '{' === $tj) {
                        break;
                    }
                }

                if (is_string($name) && '' !== $name) {
                    $typeList[] = ['kind' => $kind, 'name' => $name];
                    $firstTypeSeen = true;
                }
            }

            if (T_WHITESPACE !== $id && T_COMMENT !== $id && T_DOC_COMMENT !== $id) {
                $prevSignificant = $id;
            }
        } else {
            // Keep punctuation in scan text.
            $scanText .= (string) $t;

            if ('' !== trim((string) $t)) {
                $prevSignificant = (string) $t;
            }
        }
    }

    return [
        'namespace' => $ns,
        'typeList' => $typeList,
        'importList' => array_values(array_unique($importList)),
        'scanText' => $scanText,
    ];
}

/**
 * @return list<string>
 */
function buildKnownNamespaceList(array $knownTypeSet): array
{
    $set = [];

    foreach (array_keys($knownTypeSet) as $typeFqn) {
        $parts = explode('\\', $typeFqn);
        $acc = '';
        $max = count($parts) - 1; // exclude terminal type segment
        for ($i = 0; $i < $max; $i++) {
            $acc = '' === $acc ? $parts[$i] : $acc.'\\'.$parts[$i];
            $set[$acc] = true;
        }
    }

    return array_keys($set);
}

/**
 * @return bool true if FQCN appears to be a namespace prefix, not a concrete type
 */
function isKnownNamespacePrefix(string $fqn, array $knownNamespaceSet): bool
{
    return isset($knownNamespaceSet[$fqn]);
}

/**
 * @return list<string>
 */
function extractAppReferences(string $scanText): array
{
    // Require at least one namespace separator and a terminal identifier.
    $pattern = '/\\bApp\\\\[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+\\b/';

    if (!preg_match_all($pattern, $scanText, $m)) {
        return [];
    }

    $out = [];
    foreach ($m[0] as $raw) {
        $fqn = ltrim((string) $raw, '\\');

        if ('' === $fqn) {
            continue;
        }

        if (str_ends_with($fqn, '\\')) {
            continue;
        }

        $out[] = $fqn;
    }

    return array_values(array_unique($out));
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS)
);

$fileList = [];
foreach ($it as $node) {
    if ($node->isFile() && 'php' === strtolower((string) $node->getExtension())) {
        $fileList[] = $node->getPathname();
    }
}
sort($fileList);

$knownTypeSet = [];
$fileMetaMap = [];

foreach ($fileList as $abs) {
    $code = @file_get_contents($abs);
    if (false === $code) {
        continue;
    }

    $meta = parsePhpForMissingScan($code);
    $fileMetaMap[$abs] = $meta;

    $ns = $meta['namespace'];
    if (!is_string($ns) || '' === $ns) {
        continue;
    }

    foreach ($meta['typeList'] as $t) {
        $fqn = $ns.'\\'.$t['name'];
        $knownTypeSet[$fqn] = true;
    }
}

$knownNamespaceSet = array_fill_keys(buildKnownNamespaceList($knownTypeSet), true);

$issueList = [];
$issueKeySet = [];

/**
 * @param array<string,mixed> $row
 */
$pushIssue = static function (array $row) use (&$issueList, &$issueKeySet): void {
    $type = (string) ($row['type'] ?? 'unknown');
    $file = (string) ($row['file'] ?? '');
    $fqn = (string) ($row['fqn'] ?? '');
    $msg = (string) ($row['message'] ?? '');

    $key = $type.'|'.$file.'|'.$fqn.'|'.$msg;
    if (isset($issueKeySet[$key])) {
        return;
    }

    $issueKeySet[$key] = true;
    $issueList[] = $row;
};

foreach ($fileList as $abs) {
    $rel = str_replace('\\', '/', substr($abs, strlen($repoRoot) + 1));

    if (!isset($fileMetaMap[$abs])) {
        $pushIssue(['type' => 'read', 'file' => $rel, 'message' => 'cannot parse/read']);
        continue;
    }

    /** @var array{namespace:string|null,typeList:list<array{kind:string,name:string}>,importList:list<string>,scanText:string} $meta */
    $meta = $fileMetaMap[$abs];

    $fileNamespace = is_string($meta['namespace']) ? $meta['namespace'] : null;

    // 1) Missing App\* imports.
    foreach ($meta['importList'] as $fqn) {
        if (!str_starts_with($fqn, 'App\\')) {
            continue;
        }

        if (isset($knownTypeSet[$fqn])) {
            continue;
        }

        // Namespace aliases are legal and common; don't treat them as missing types.
        if (isKnownNamespacePrefix($fqn, $knownNamespaceSet)) {
            continue;
        }

        $pushIssue(['type' => 'import', 'file' => $rel, 'fqn' => $fqn]);
    }

    // 2) Missing App\* references in code-only text.
    foreach (extractAppReferences($meta['scanText']) as $fqn) {
        if (isset($knownTypeSet[$fqn])) {
            continue;
        }

        if (isKnownNamespacePrefix($fqn, $knownNamespaceSet)) {
            continue;
        }

        if (is_string($fileNamespace) && '' !== $fileNamespace && $fqn === $fileNamespace) {
            continue;
        }

        $pushIssue(['type' => 'reference', 'file' => $rel, 'fqn' => $fqn]);
    }
}

usort(
    $issueList,
    static function (array $a, array $b): int {
        $ak = ($a['type'] ?? '').'|'.($a['file'] ?? '').'|'.($a['fqn'] ?? '').'|'.($a['message'] ?? '');
        $bk = ($b['type'] ?? '').'|'.($b['file'] ?? '').'|'.($b['fqn'] ?? '').'|'.($b['message'] ?? '');

        return strcmp((string) $ak, (string) $bk);
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

    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

echo "Vendoring missing class scan (v2)\n";
echo "- PHP files scanned: " . count($fileList) . "\n";
echo "- Known types: " . count($knownTypeSet) . "\n";
echo "- Known namespaces: " . count($knownNamespaceSet) . "\n";
echo "- Issue count: " . count($issueList) . "\n";

$printList = $limit > 0 ? array_slice($issueList, 0, $limit) : $issueList;
foreach ($printList as $issue) {
    $type = (string) ($issue['type'] ?? 'issue');
    $file = (string) ($issue['file'] ?? '');
    $line = isset($issue['line']) ? (int) $issue['line'] : 0;
    $message = (string) ($issue['message'] ?? '');

    echo sprintf('- [%s] %s', $type, $file);
    if ($line > 0) {
        echo ':' . $line;
    }
    if ('' !== $message) {
        echo ' — ' . $message;
    }
    echo "\n";
}

exit(0);
