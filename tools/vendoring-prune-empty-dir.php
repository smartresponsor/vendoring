<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI empty-directory pruner for Vendoring.
 * Focus: remove empty scaffold directories that pollute snapshots/archives.
 * Safety: ONLY removes directories that are empty after recursively pruning children.
 * Not a formatter; no code rewriting.
 */

$repoRoot = realpath(__DIR__ . '/..') ?: getcwd();
if (!is_string($repoRoot) || '' === $repoRoot) {
    fwrite(STDERR, "ERROR: cannot resolve repo root\n");
    exit(2);
}

$args = $argv;
array_shift($args);
$dryRun = in_array('--dry-run', $args, true) || in_array('--dry', $args, true);

$rootRel = 'src';
foreach ($args as $arg) {
    if (str_starts_with($arg, '--root=')) {
        $rootRel = (string) substr($arg, strlen('--root='));
        $rootRel = trim($rootRel);
    }
}

$rootAbs = $repoRoot . DIRECTORY_SEPARATOR . $rootRel;
if (!is_dir($rootAbs)) {
    fwrite(STDERR, "ERROR: root not found: {$rootRel}\n");
    exit(2);
}

/**
 * @return array{removed:int, visited:int}
 */
function pruneEmptyDir(string $dirAbs, bool $dryRun, string $repoRoot): array
{
    $removed = 0;
    $visited = 0;

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirAbs, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($it as $node) {
        if (!$node->isDir()) {
            continue;
        }

        $visited++;
        $path = $node->getPathname();

        // If directory is empty (no files, no subdirs), remove.
        $handle = opendir($path);
        if (false === $handle) {
            fwrite(STDERR, sprintf("WARN: cannot open directory: %s\n", $path));
            continue;
        }

        $hasChild = false;
        while (false !== ($entry = readdir($handle))) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }
            $hasChild = true;
            break;
        }
        closedir($handle);

        if ($hasChild) {
            continue;
        }

        $rel = str_replace('\\', '/', substr($path, strlen($repoRoot) + 1));
        echo ($dryRun ? '[DRY] ' : '') . "rmdir {$rel}\n";

        if (!$dryRun) {
            if (!rmdir($path)) {
                fwrite(STDERR, sprintf("WARN: failed to remove directory: %s\n", $path));
                continue;
            }
        }
        $removed++;
    }

    // Lastly, try to remove the root itself if empty (optional behavior)
    $handle = opendir($dirAbs);
    if (false !== $handle) {
        $hasChild = false;
        while (false !== ($entry = readdir($handle))) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }
            $hasChild = true;
            break;
        }
        closedir($handle);

        if (!$hasChild) {
            $rel = str_replace('\\', '/', substr($dirAbs, strlen($repoRoot) + 1));
            echo ($dryRun ? '[DRY] ' : '') . "rmdir {$rel}\n";
            if (!$dryRun) {
                if (!rmdir($dirAbs)) {
                    fwrite(STDERR, sprintf("WARN: failed to remove directory: %s\n", $dirAbs));
                    return ['removed' => $removed, 'visited' => $visited];
                }
            }
            $removed++;
        }
    } else {
        fwrite(STDERR, sprintf("WARN: cannot open directory: %s\n", $dirAbs));
    }

    return ['removed' => $removed, 'visited' => $visited];
}

echo "Vendoring empty-dir prune\n";
echo "- root: {$rootRel}\n";
echo '- mode: ' . ($dryRun ? 'dry-run' : 'apply') . "\n";

$stats = pruneEmptyDir($rootAbs, $dryRun, $repoRoot);
echo "- visited dir: {$stats['visited']}\n";
echo "- removed dir: {$stats['removed']}\n";

exit(0);
