<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.'/bin/generate-phpdocumentor-site.php');
passthru($cmd, $code);
if (0 !== $code) {
    fwrite(STDERR, "phpDocumentor site generation failed.\n");
    exit($code);
}

foreach (['phpdoc.dist.xml', 'build/docs/phpdocumentor/index.html'] as $path) {
    if (!is_file($root.'/'.$path)) {
        fwrite(STDERR, sprintf("Missing phpDocumentor artifact: %s\n", $path));
        exit(1);
    }
}

echo "phpDocumentor config smoke OK\n";
