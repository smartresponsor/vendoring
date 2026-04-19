<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * Missing class scan for Vendoring.
 *
 * Scope:
 * - App\Vendoring\\* imports that do not resolve to a known type in this repository.
 * - App\Vendoring\\* fully-qualified references (best-effort regex) that do not resolve.
 *
 * Non-goals:
 * - Formatting, auto-fixing, or deep static analysis.
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
foreach ($args as $a) {
    if (str_starts_with($a, '--limit=')) {
        $v = (int) substr($a, strlen('--limit='));
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

/**
 * @return array{namespace: string|null, typeList: list<array{kind:string,name:string}>, importList: list<string>}
 */
function parsePhp(string $code): array
{
    $tokens = token_get_all($code);

    $ns = null;
    $typeList = [];
    $importList = [];

    $n = count($tokens);
    $prevSignificant = null;
    $firstTypeSeen = false;

    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];

        if (is_array($t)) {
            $id = $t[0];

            if (T_NAMESPACE === $id) {
                $nsParts = [];
                for ($j = $i + 1; $j < $n; $j++) {
                    $tj = $tokens[$j];
                    if (is_array($tj) && (T_STRING === $tj[0] || T_NAME_QUALIFIED === $tj[0] || T_NS_SEPARATOR === $tj[0])) {
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
                // Ignore closure use: preceded by ')', or followed by '('.
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

                    $chunk = trim($chunk);
                    $chunk = preg_replace('/^(function|const)\s+/i', '', $chunk) ?? $chunk;

                    $parts = array_values(array_filter(array_map('trim', explode(',', $chunk)), static fn(string $v): bool => '' !== $v));
                    foreach ($parts as $p) {
                        $p = preg_replace('/\s+/', ' ', $p) ?? $p;

                        $fqn = $p;
                        if (preg_match('/^(.+?)\s+as\s+([A-Za-z_][A-Za-z0-9_]*)$/i', $p, $m)) {
                            $fqn = trim($m[1]);
                        }

                        $fqn = preg_replace('/\s+/', '', $fqn) ?? $fqn;
                        $fqn = ltrim($fqn, '\\');

                        if ('' !== $fqn) {
                            $importList[] = $fqn;
                        }
                    }
                }
            }

            if (T_CLASS === $id || T_INTERFACE === $id || T_TRAIT === $id || (defined('T_ENUM') && T_ENUM === $id)) {
                // Ignore "::class" constant fetch.
                if (T_DOUBLE_COLON === $prevSignificant) {
                    continue;
                }

                // Ignore anonymous class: "new class".
                if (T_NEW === $prevSignificant) {
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
            if ('' !== trim((string) $t)) {
                $prevSignificant = (string) $t;
            }
        }
    }

    return ['namespace' => $ns, 'typeList' => $typeList, 'importList' => $importList];
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS),
);

$fileList = [];
foreach ($it as $node) {
    if ($node->isFile() && 'php' === strtolower((string) $node->getExtension())) {
        $fileList[] = $node->getPathname();
    }
}

$knownTypeSet = [];

foreach ($fileList as $abs) {
    $code = file_get_contents($abs);
    if (false === $code) {
        continue;
    }

    $meta = parsePhp($code);
    $ns = $meta['namespace'];
    if (!is_string($ns) || '' === $ns) {
        continue;
    }

    foreach ($meta['typeList'] as $t) {
        $fqn = $ns . '\\' . $t['name'];
        $knownTypeSet[$fqn] = true;
    }
}

$issueList = [];

foreach ($fileList as $abs) {
    $rel = str_replace('\\', '/', substr($abs, strlen($repoRoot) + 1));

    $code = file_get_contents($abs);
    if (false === $code) {
        $issueList[] = ['type' => 'read', 'file' => $rel, 'message' => 'cannot read'];
        continue;
    }

    $meta = parsePhp($code);

    // 1) Missing App\Vendoring\\* imports.
    foreach ($meta['importList'] as $fqn) {
        if (!str_starts_with($fqn, 'App\Vendoring\\')) {
            continue;
        }
        if (!isset($knownTypeSet[$fqn])) {
            $issueList[] = ['type' => 'import', 'file' => $rel, 'fqn' => $fqn];
        }
    }

    // 2) Best-effort missing fully qualified references in code.
    // Note: this may catch doc comments; we accept some noise (report-only).
    if (preg_match_all('/\\\\?App\Vendoring\\\\[A-Za-z_][A-Za-z0-9_\\\\]*/', $code, $m)) {
        foreach (array_unique($m[0]) as $raw) {
            $fqn = ltrim($raw, '\\');
            if (!isset($knownTypeSet[$fqn])) {
                $issueList[] = ['type' => 'reference', 'file' => $rel, 'fqn' => $fqn];
            }
        }
    }
}

// Normalize + cap output.
$issueList = array_values($issueList);

if ($asJson) {
    $out = [
        'fileCount' => count($fileList),
        'knownTypeCount' => count($knownTypeSet),
        'issueCount' => count($issueList),
        'issueList' => $limit > 0 ? array_slice($issueList, 0, $limit) : $issueList,
    ];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(($strict && count($issueList) > 0) ? 1 : 0);
}

echo "Vendoring missing class scan\n";
echo '- PHP files under src/: ' . count($fileList) . "\n";
echo '- Known types: ' . count($knownTypeSet) . "\n";
echo '- Issue count: ' . count($issueList) . "\n";

$shown = 0;
foreach ($issueList as $it) {
    if ($shown >= $limit) {
        echo "  ... (limit reached)\n";
        break;
    }

    if ('read' === $it['type']) {
        echo "  * [READ] {$it['file']} {$it['message']}\n";
        $shown++;
        continue;
    }

    $tag = strtoupper((string) $it['type']);
    echo "  * [$tag] {$it['file']} {$it['fqn']}\n";
    $shown++;
}

exit(($strict && count($issueList) > 0) ? 1 : 0);
