<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/.idea/workspace.xml';

if (file_exists($path)) {
    fwrite(STDERR, "Forbidden IDE runtime artifact remains committed: .idea/workspace.xml\n");
    exit(1);
}

echo "idea-runtime-artifact smoke passed\n";
