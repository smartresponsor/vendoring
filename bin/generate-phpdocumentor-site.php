<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$outDir = $root . '/build/docs/phpdocumentor';
if (!is_dir($outDir) && !mkdir($outDir, 0777, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Failed to create phpDocumentor output directory.\n");
    exit(1);
}

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendoring phpDocumentor RC Surface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #222; }
        code { background: #f4f4f4; padding: 0.1rem 0.25rem; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Vendoring phpDocumentor RC Surface</h1>
    <p>This placeholder artifact is produced from repository-owned generation scripts so CI can publish a stable documentation bundle before the full phpDocumentor binary is wired in.</p>
    <div class="card">
        <h2>Expected production tools</h2>
        <ul>
            <li><code>phpdocumentor/phpdocumentor</code> for navigable API docs</li>
            <li><code>nelmio/api-doc-bundle</code> for Symfony-native API browsing</li>
            <li>OpenAPI artifacts published from CI for release-candidate evidence</li>
        </ul>
    </div>
    <div class="card">
        <h2>Current generated artifacts</h2>
        <ul>
            <li><code>build/docs/openapi.json</code></li>
            <li><code>build/docs/openapi.yaml</code></li>
            <li><code>phpdoc.dist.xml</code></li>
        </ul>
    </div>
</body>
</html>
HTML;

file_put_contents($outDir . '/index.html', $html . PHP_EOL);
echo "Generated build/docs/phpdocumentor/index.html\n";
