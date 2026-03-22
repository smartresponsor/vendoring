<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src'));

foreach ($iterator as $file) {
    if (!$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $contents = (string) file_get_contents($file->getPathname());

    if (false !== stripos($contents, 'stub')) {
        fwrite(STDERR, 'Forbidden stub marker remains in source: '.$file->getPathname().PHP_EOL);
        exit(1);
    }
}

echo "no-stub-source smoke passed\n";
