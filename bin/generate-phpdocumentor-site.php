<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$outDir = $root.'/build/docs/phpdocumentor';
if (!is_dir($outDir) && !mkdir($outDir, 0777, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Failed to create phpDocumentor output directory.\n");
    exit(1);
}

$generatedAt = date(DATE_ATOM);

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendoring phpDocumentor Reference Surface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #222; line-height: 1.5; }
        code { background: #f4f4f4; padding: 0.1rem 0.25rem; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        ul { margin-top: 0.5rem; }
        .meta { color: #555; }
    </style>
</head>
<body>
    <h1>Vendoring phpDocumentor Reference Surface</h1>
    <p class="meta">Generated at: __GENERATED_AT__</p>
    <p>This repository publishes a stable generated reference landing page so CI and release-candidate review can point to a predictable code-reference surface even before full binary-driven phpDocumentor publishing is enabled everywhere.</p>

    <div class="card">
        <h2>Reference role</h2>
        <ul>
            <li><code>phpdoc.dist.xml</code> remains the repository-owned phpDocumentor configuration seam.</li>
            <li><code>build/docs/phpdocumentor/index.html</code> remains the generated code-reference entry point.</li>
            <li>Hand-written narrative documentation stays under <code>docs/</code> and the Antora producer surface under <code>docs/modules/ROOT/pages/</code>.</li>
        </ul>
    </div>

    <div class="card">
        <h2>Related generated artifacts</h2>
        <ul>
            <li><code>build/docs/openapi.json</code></li>
            <li><code>build/docs/openapi.yaml</code></li>
            <li><code>build/release/rc-evidence.json</code></li>
            <li><code>build/release/release-manifest.json</code></li>
            <li><code>build/release/rollback-manifest.json</code></li>
        </ul>
    </div>

    <div class="card">
        <h2>Repository-owned narrative companions</h2>
        <ul>
            <li><code>docs/release/RC_DOCUMENTATION_SURFACES.md</code></li>
            <li><code>docs/release/RC_OPENAPI_SURFACE.md</code></li>
            <li><code>docs/release/RC_PHPDOCUMENTOR_SURFACE.md</code></li>
            <li><code>docs/modules/ROOT/pages/reference.adoc</code></li>
        </ul>
    </div>

    <div class="card">
        <h2>Next enrichment step</h2>
        <p>When the full <code>phpdocumentor/phpdocumentor</code> binary is wired into the aligned publishing/runtime lane, this entry page can be replaced by navigable generated API documentation without changing the repository boundary model.</p>
    </div>
</body>
</html>
HTML;

$html = str_replace('__GENERATED_AT__', htmlspecialchars($generatedAt, ENT_QUOTES), $html);
file_put_contents($outDir.'/index.html', $html.PHP_EOL);
echo "Generated build/docs/phpdocumentor/index.html\n";
