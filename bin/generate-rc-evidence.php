<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$releaseDir = $root.'/build/release';
$runtimeDir = $root.'/build/docs/runtime';

@mkdir($releaseDir, 0777, true);
@mkdir($runtimeDir, 0777, true);

$releaseDocs = [
    'rcBaseline' => is_file($root.'/docs/release/RC_BASELINE.md'),
    'rcRuntimeSurfaces' => is_file($root.'/docs/release/RC_RUNTIME_SURFACES.md'),
    'rcOperatorSurface' => is_file($root.'/docs/release/RC_OPERATOR_SURFACE.md'),
    'rcDocumentationSurfaces' => is_file($root.'/docs/release/RC_DOCUMENTATION_SURFACES.md'),
    'rcEvidencePack' => is_file($root.'/docs/release/RC_EVIDENCE_PACK.md'),
    'rcKnownNonBlockers' => is_file($root.'/docs/release/RC_KNOWN_NON_BLOCKERS.md'),
    'rcOpenApiSurface' => is_file($root.'/docs/release/RC_OPENAPI_SURFACE.md'),
    'rcPhpDocumentorSurface' => is_file($root.'/docs/release/RC_PHPDOCUMENTOR_SURFACE.md'),
    'rcReleaseManifest' => is_file($root.'/docs/release/RC_RELEASE_MANIFEST.md'),
    'rcRollbackManifest' => is_file($root.'/docs/release/RC_ROLLBACK_MANIFEST.md'),
];

$documentationSurfaces = [
    'antora' => [
        'descriptor' => is_file($root.'/docs/antora.yml'),
        'navigation' => is_file($root.'/docs/modules/ROOT/nav.adoc'),
        'indexPage' => is_file($root.'/docs/modules/ROOT/pages/index.adoc'),
        'architecturePage' => is_file($root.'/docs/modules/ROOT/pages/architecture.adoc'),
        'installPage' => is_file($root.'/docs/modules/ROOT/pages/install.adoc'),
        'operationsPage' => is_file($root.'/docs/modules/ROOT/pages/operations.adoc'),
        'apiPage' => is_file($root.'/docs/modules/ROOT/pages/api.adoc'),
        'referencePage' => is_file($root.'/docs/modules/ROOT/pages/reference.adoc'),
    ],
    'openapi' => [
        'json' => is_file($root.'/build/docs/openapi.json'),
        'yaml' => is_file($root.'/build/docs/openapi.yaml'),
        'generator' => is_file($root.'/bin/generate-openapi.php'),
    ],
    'phpdocumentor' => [
        'config' => is_file($root.'/phpdoc.dist.xml'),
        'generator' => is_file($root.'/bin/generate-phpdocumentor-site.php'),
        'index' => is_file($root.'/build/docs/phpdocumentor/index.html'),
    ],
];

$composerScripts = [];
$composerJsonPath = $root.'/composer.json';
if (is_file($composerJsonPath)) {
    $composer = json_decode((string) file_get_contents($composerJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
    foreach (['docs:rc-evidence', 'docs:release-manifest', 'quality:docs', 'quality:release-candidate', 'test:rc-evidence', 'test:release-candidate-docs'] as $name) {
        $composerScripts[$name] = array_key_exists($name, $scripts);
    }
}

$status = 'ok';
foreach ([$releaseDocs, $composerScripts] as $flatSection) {
    if (in_array(false, $flatSection, true)) {
        $status = 'warn';
        break;
    }
}
foreach ($documentationSurfaces as $section) {
    if (in_array(false, $section, true)) {
        $status = 'warn';
        break;
    }
}

$evidence = [
    'status' => $status,
    'generatedAt' => date(DATE_ATOM),
    'repository' => 'vendoring',
    'provenance' => [
        'generator' => 'bin/generate-rc-evidence.php',
        'releaseManifestGenerator' => 'bin/generate-release-manifest.php',
    ],
    'releaseDocs' => $releaseDocs,
    'documentationSurfaces' => $documentationSurfaces,
    'composerScripts' => $composerScripts,
];

file_put_contents(
    $releaseDir.'/rc-evidence.json',
    json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
);

$markdown = [
    '# RC evidence',
    '',
    '- Status: `'.$status.'`',
    '- Generated at: `'.$evidence['generatedAt'].'`',
    '- Repository: `vendoring`',
    '',
    '## Release docs',
];
foreach ($releaseDocs as $name => $present) {
    $markdown[] = '- '.$name.': '.($present ? 'present' : 'missing');
}
$markdown[] = '';
$markdown[] = '## Documentation surfaces';
foreach ($documentationSurfaces as $group => $checks) {
    $markdown[] = '';
    $markdown[] = '### '.$group;
    foreach ($checks as $name => $present) {
        $markdown[] = '- '.$name.': '.($present ? 'present' : 'missing');
    }
}
$markdown[] = '';
$markdown[] = '## Composer lanes';
foreach ($composerScripts as $name => $present) {
    $markdown[] = '- '.$name.': '.($present ? 'present' : 'missing');
}
$markdown[] = '';
$markdown[] = '## Boundary note';
$markdown[] = 'This evidence pack is repository-local RC proof. Central portal/site assembly remains outside this producer repository.';
$markdown[] = '';
file_put_contents($releaseDir.'/rc-evidence.md', implode(PHP_EOL, $markdown));

$runtimeIndex = [
    'Vendoring runtime/documentation evidence index',
    'status='.$status,
    'generatedAt='.$evidence['generatedAt'],
    'openapiJson='.(is_file($root.'/build/docs/openapi.json') ? 'present' : 'missing'),
    'openapiYaml='.(is_file($root.'/build/docs/openapi.yaml') ? 'present' : 'missing'),
    'phpdocumentorIndex='.(is_file($root.'/build/docs/phpdocumentor/index.html') ? 'present' : 'missing'),
    'antoraDescriptor='.(is_file($root.'/docs/antora.yml') ? 'present' : 'missing'),
];
file_put_contents($runtimeDir.'/index.txt', implode(PHP_EOL, $runtimeIndex).PHP_EOL);

echo "rc evidence generated\n";

require __DIR__.'/generate-release-manifest.php';
