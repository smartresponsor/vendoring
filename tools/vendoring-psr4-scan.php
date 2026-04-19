<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * PSR-4-ish structural scan for Vendoring.
 * Scope: namespace ↔ path matching, primary type ↔ filename matching, import alias collisions.
 * No formatting, no auto-fix.
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

$srcRoot = $repoRoot . DIRECTORY_SEPARATOR . 'src';
if (!is_dir($srcRoot)) {
    fwrite(STDERR, "ERROR: src/ not found\n");
    exit(2);
}

/**
 * @return array{namespace: string|null, typeList: list<array{kind:string,name:string,offset:int}>, importAliasMap: array<string,string>, importDupMap: array<string,int>}
 */
function parsePhpMeta(string $code): array
{
    // token_get_all() treats "<?phpdeclare" as inline HTML; normalize missing whitespace after open tag.
    if (str_starts_with($code, '<?php') && isset($code[5]) && !ctype_space($code[5])) {
        $code = "<?php\n" . substr($code, 5);
    }

    $tokens = token_get_all($code);
    $ns = null;
    $typeList = [];

    $importAliasMap = [];
    $importDupMap = [];

    $firstTypeSeen = false;

    $n = count($tokens);

    $prevSignificant = null;

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
                    // Parse a use statement until ';'.
                    $chunk = '';
                    for ($j = $i + 1; $j < $n; $j++) {
                        $tj = $tokens[$j];
                        if (';' === $tj) {
                            break;
                        }
                        $chunk .= is_array($tj) ? $tj[1] : $tj;
                    }

                    $chunk = trim($chunk);
                    // Strip use function/const.
                    $chunk = preg_replace('/^(function|const)\s+/i', '', $chunk) ?? $chunk;

                    $parts = array_values(array_filter(array_map('trim', explode(',', $chunk)), static fn(string $v): bool => '' !== $v));
                    foreach ($parts as $p) {
                        // Normalize spaces.
                        $p = preg_replace('/\s+/', ' ', $p) ?? $p;

                        $alias = null;
                        $fqn = $p;
                        if (preg_match('/^(.+?)\s+as\s+([A-Za-z_][A-Za-z0-9_]*)$/i', $p, $m)) {
                            $fqn = trim($m[1]);
                            $alias = trim($m[2]);
                        }

                        $fqn = ltrim($fqn);
                        $fqn = preg_replace('/\s+/', '', $fqn) ?? $fqn;
                        $fqn = ltrim($fqn, '\\');

                        if (null === $alias || '' === $alias) {
                            $seg = explode('\\', $fqn);
                            $alias = end($seg) ?: $fqn;
                        }

                        if (isset($importAliasMap[$alias]) && $importAliasMap[$alias] !== $fqn) {
                            // Mark collision by storing a special value; full report built by caller.
                            $importAliasMap[$alias] = $importAliasMap[$alias] . ' | ' . $fqn;
                        } else {
                            $importAliasMap[$alias] = $fqn;
                        }

                        $importDupMap[$fqn] = ($importDupMap[$fqn] ?? 0) + 1;
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
                    $typeList[] = ['kind' => $kind, 'name' => $name, 'offset' => $t[2] ?? 0];
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

    return [
        'namespace' => $ns,
        'typeList' => $typeList,
        'importAliasMap' => $importAliasMap,
        'importDupMap' => $importDupMap,
    ];
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS),
);

$issueList = [];

foreach ($it as $node) {
    if (!$node->isFile() || 'php' !== strtolower((string) $node->getExtension())) {
        continue;
    }

    $abs = $node->getPathname();
    $rel = str_replace('\\', '/', substr($abs, strlen($repoRoot) + 1));

    $code = file_get_contents($abs);
    if (false === $code) {
        $issueList[] = ['type' => 'read', 'file' => $rel, 'message' => 'cannot read'];
        continue;
    }

    $meta = parsePhpMeta($code);

    $dirRel = dirname($rel);
    $dirRel = str_replace('\\', '/', $dirRel);

    // Expected namespace: App + path under src.
    $expectedNs = 'App';
    if (str_starts_with($dirRel . '/', 'src/')) {
        $suffix = substr($dirRel, strlen('src/'));
        // Legacy path alias: src/ServiceInterface/<Domain>/Service/<X> -> App\Vendoring\ServiceInterface\<Domain>\<X>
        $suffix = preg_replace('#^ServiceInterface/([^/]+)/Service/#', 'ServiceInterface/$1/', $suffix) ?? $suffix;
        if ('' !== $suffix && '.' !== $suffix) {
            $expectedNs .= '\\' . str_replace('/', '\\', $suffix);
        }
    }

    if (null === $meta['namespace'] || '' === $meta['namespace']) {
        $issueList[] = ['type' => 'namespace', 'file' => $rel, 'message' => 'missing namespace', 'expected' => $expectedNs];
    } elseif ($meta['namespace'] !== $expectedNs) {
        $issueList[] = ['type' => 'namespace', 'file' => $rel, 'message' => 'namespace mismatch', 'expected' => $expectedNs, 'actual' => $meta['namespace']];
    }

    $base = pathinfo($rel, PATHINFO_FILENAME);
    $typeList = $meta['typeList'];

    if (0 === count($typeList)) {
        // Not necessarily an issue: could be a functions file. Mark as info.
        $issueList[] = ['type' => 'type', 'file' => $rel, 'message' => 'no primary type found'];
    } else {
        if (count($typeList) > 1) {
            $issueList[] = ['type' => 'type', 'file' => $rel, 'message' => 'multiple types found', 'types' => $typeList];
        }

        $primary = $typeList[0]['name'];
        if ($primary !== $base) {
            $issueList[] = ['type' => 'name', 'file' => $rel, 'message' => 'filename mismatch', 'expected' => $primary . '.php', 'actual' => $base . '.php'];
        }
    }

    // Import collisions: alias -> multiple FQCNs
    foreach ($meta['importAliasMap'] as $alias => $fqn) {
        if (str_contains($fqn, ' | ')) {
            $issueList[] = ['type' => 'import', 'file' => $rel, 'message' => 'import alias collision', 'alias' => $alias, 'fqn' => $fqn];
        }
    }

    // Duplicate identical imports (same FQCN repeated)
    foreach ($meta['importDupMap'] as $fqn => $cnt) {
        if ($cnt > 1) {
            $issueList[] = ['type' => 'import', 'file' => $rel, 'message' => 'duplicate import', 'fqn' => $fqn, 'count' => $cnt];
        }
    }
}

$result = [
    'phpFiles' => iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS))),
    'issues' => $issueList,
];

if ($asJson) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "Vendoring PSR scan\n";
    $cnt = count($issueList);
    echo "- Issue count: {$cnt}\n";

    $max = 200;
    $shown = 0;
    foreach ($issueList as $iss) {
        if ($shown >= $max) {
            echo "- ... truncated (>{$max})\n";
            break;
        }

        $type = $iss['type'];
        $file = $iss['file'];
        $msg = $iss['message'];

        if ('namespace' === $type && isset($iss['expected'], $iss['actual'])) {
            echo "  * [NS] {$file} expected={$iss['expected']} actual={$iss['actual']}\n";
        } elseif ('namespace' === $type && isset($iss['expected'])) {
            echo "  * [NS] {$file} expected={$iss['expected']} ({$msg})\n";
        } elseif ('name' === $type) {
            echo "  * [NAME] {$file} expected={$iss['expected']} actual={$iss['actual']}\n";
        } elseif ('type' === $type && isset($iss['types'])) {
            echo "  * [TYPE] {$file} {$msg} (" . count((array) $iss['types']) . ")\n";
        } elseif ('import' === $type) {
            if (isset($iss['alias'], $iss['fqn'])) {
                echo "  * [IMPORT] {$file} {$msg} alias={$iss['alias']} fqn={$iss['fqn']}\n";
            } elseif (isset($iss['fqn'], $iss['count'])) {
                echo "  * [IMPORT] {$file} {$msg} fqn={$iss['fqn']} count={$iss['count']}\n";
            } else {
                echo "  * [IMPORT] {$file} {$msg}\n";
            }
        } else {
            echo "  * [{$type}] {$file} {$msg}\n";
        }

        $shown++;
    }
}

if ($strict && count($issueList) > 0) {
    exit(1);
}

exit(0);
