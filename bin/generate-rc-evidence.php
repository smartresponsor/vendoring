<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$docsDir = $root . '/build/docs';
$releaseDir = $root . '/build/release';

foreach ([$docsDir, $releaseDir] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        fwrite(STDERR, sprintf("Failed to create directory: %s\n", $dir));
        exit(1);
    }
}

$composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];
$require = $composer['require'] ?? [];
$requireDev = $composer['require-dev'] ?? [];

$lanes = [
    'static' => ['script' => 'quality:static', 'purpose' => 'syntax, formatting and static analysis'],
    'contracts' => ['script' => 'quality:contracts', 'purpose' => 'container, contracts and repository hygiene'],
    'runtime' => ['script' => 'quality:runtime', 'purpose' => 'Symfony runtime and HTTP vertical slices'],
    'persistence' => ['script' => 'quality:persistence', 'purpose' => 'Doctrine, schema and database-backed proofs'],
    'api' => ['script' => 'quality:api', 'purpose' => 'JSON, policy, idempotency and error surface'],
    'ui' => ['script' => 'quality:ui', 'purpose' => 'server-rendered operator surface'],
    'docs' => ['script' => 'quality:docs', 'purpose' => 'OpenAPI, phpDocumentor and RC documentation artifacts'],
    'release_candidate' => ['script' => 'quality:release-candidate', 'purpose' => 'aggregate RC umbrella lane'],
];

$routeEvidence = [
    '/api/vendor-transactions' => 'JSON create transaction surface',
    '/api/vendor-transactions/vendor/{vendorId}' => 'JSON list by vendor surface',
    '/api/vendor-transactions/vendor/{vendorId}/{id}/status' => 'JSON status update surface',
    '/ops/vendor-transactions/{vendorId}' => 'server-rendered operator read surface',
    '/api/doc' => 'conditional Nelmio browsing seam',
];

$artifacts = [
    'openapi_json' => file_exists($docsDir . '/openapi.json') ? 'build/docs/openapi.json' : null,
    'openapi_yaml' => file_exists($docsDir . '/openapi.yaml') ? 'build/docs/openapi.yaml' : null,
    'phpdocumentor_index' => file_exists($docsDir . '/phpdocumentor/index.html') ? 'build/docs/phpdocumentor/index.html' : null,
    'phpdoc_dist' => file_exists($root . '/phpdoc.dist.xml') ? 'phpdoc.dist.xml' : null,
    'nelmio_runtime_route' => file_exists($root . '/config/routes/nelmio_api_doc.yaml') ? 'config/routes/nelmio_api_doc.yaml' : null,
];

$workflowEvidence = [
    'quality' => '.github/workflows/quality.yml',
    'runtime' => '.github/workflows/runtime.yml',
    'docs' => '.github/workflows/docs.yml',
    'ui_smoke' => '.github/workflows/ui-smoke.yml',
    'release_candidate' => '.github/workflows/release-candidate.yml',
];

$runtimePackages = [
    'twig/twig',
    'symfony/twig-bundle',
    'symfony/form',
    'symfony/validator',
    'symfony/security-csrf',
    'nelmio/api-doc-bundle',
];

$presentRuntimePackages = [];
foreach ($runtimePackages as $package) {
    if (isset($require[$package]) || isset($requireDev[$package])) {
        $presentRuntimePackages[$package] = $require[$package] ?? $requireDev[$package];
    }
}

$evidence = [
    'component' => [
        'name' => $composer['name'] ?? 'unknown',
        'type' => $composer['type'] ?? 'project',
        'release_candidate_stage' => 'RC evidence finalization',
        'current_shape' => 'headless/backend component with minimal operator surface and generated documentation artifacts',
    ],
    'quality_lanes' => $lanes,
    'route_evidence' => $routeEvidence,
    'artifacts' => array_filter($artifacts),
    'workflows' => $workflowEvidence,
    'runtime_packages' => $presentRuntimePackages,
    'docs' => [
        'roadmap' => 'docs/release/RC_ROADMAP.md',
        'ci_lanes' => 'docs/release/RC_CI_LANES.md',
        'runtime_surfaces' => 'docs/release/RC_RUNTIME_SURFACES.md',
        'documentation_surfaces' => 'docs/release/RC_DOCUMENTATION_SURFACES.md',
        'runtime_activation' => 'docs/release/RC_RUNTIME_ACTIVATION.md',
        'evidence_pack' => 'docs/release/RC_EVIDENCE_PACK.md',
    ],
    'build_commands' => [
        'composer quality:release-candidate',
        'composer docs:build',
        'composer docs:rc-evidence',
    ],
];

$json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
file_put_contents($releaseDir . '/rc-evidence.json', $json);

$markdown = "# Vendoring RC Evidence Pack\n\n"
    . "This generated manifest turns the release-candidate state into a downloadable bundle.\n\n"
    . "## Component\n\n"
    . sprintf("- Name: `%s`\n", $evidence['component']['name'])
    . sprintf("- Type: `%s`\n", $evidence['component']['type'])
    . sprintf("- Stage: `%s`\n", $evidence['component']['release_candidate_stage'])
    . sprintf("- Shape: %s\n\n", $evidence['component']['current_shape'])
    . "## Quality lanes\n\n";

foreach ($lanes as $lane => $metadata) {
    $markdown .= sprintf("- `%s` via `%s` — %s\n", $lane, $metadata['script'], $metadata['purpose']);
}

$markdown .= "\n## Runtime and docs evidence\n\n";
foreach ($routeEvidence as $route => $purpose) {
    $markdown .= sprintf("- `%s` — %s\n", $route, $purpose);
}

$markdown .= "\n## Generated artifacts\n\n";
foreach (array_filter($artifacts) as $artifact) {
    $markdown .= sprintf("- `%s`\n", $artifact);
}

$markdown .= "\n## Workflow coverage\n\n";
foreach ($workflowEvidence as $name => $path) {
    $markdown .= sprintf("- `%s` => `%s`\n", $name, $path);
}

$markdown .= "\n## Activation packages\n\n";
foreach ($presentRuntimePackages as $package => $constraint) {
    $markdown .= sprintf("- `%s`: `%s`\n", $package, $constraint);
}

$markdown .= "\n## Primary commands\n\n";
foreach ($evidence['build_commands'] as $command) {
    $markdown .= sprintf("- `%s`\n", $command);
}

file_put_contents($releaseDir . '/rc-evidence.md', $markdown);

echo "Generated build/release/rc-evidence.json\n";
echo "Generated build/release/rc-evidence.md\n";
