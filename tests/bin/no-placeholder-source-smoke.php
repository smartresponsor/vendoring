<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2).'/src';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($iterator as $file) {
    if (!$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $path = $file->getPathname();
    $contents = (string) file_get_contents($path);

    if (false !== stripos($contents, 'placeholder')) {
        fwrite(STDERR, "Forbidden placeholder marker remains in: {$path}\n");
        exit(1);
    }
}

echo "no-placeholder-source smoke passed\n";
