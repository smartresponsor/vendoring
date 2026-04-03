<?php

declare(strict_types=1);

$root = dirname(__DIR__);
@mkdir($root.'/build/release', 0777, true);

$releaseDocs = [
    'rcBaseline' => file_exists($root.'/docs/release/RC_BASELINE.md'),
    'rcRuntimeSurfaces' => file_exists($root.'/docs/release/RC_RUNTIME_SURFACES.md'),
    'rcOperatorSurface' => file_exists($root.'/docs/release/RC_OPERATOR_SURFACE.md'),
    'rcEvidencePack' => file_exists($root.'/docs/release/RC_EVIDENCE_PACK.md'),
    'rcRollbackManifest' => file_exists($root.'/docs/release/RC_ROLLBACK_MANIFEST.md'),
    'rcReleaseManifest' => file_exists($root.'/docs/release/RC_RELEASE_MANIFEST.md'),
];
$buildArtifacts = [
    'rcEvidenceJson' => file_exists($root.'/build/release/rc-evidence.json'),
    'rcEvidenceMd' => file_exists($root.'/build/release/rc-evidence.md'),
    'phpdocumentorIndex' => file_exists($root.'/build/docs/phpdocumentor/index.html'),
];
$manifest = [
    'generatedAt' => date(DATE_ATOM),
    'releaseDocs' => $releaseDocs,
    'buildArtifacts' => $buildArtifacts,
    'status' => in_array(false, $releaseDocs, true) || in_array(false, $buildArtifacts, true) ? 'warn' : 'ok',
];
$rollback = [
    'generatedAt' => date(DATE_ATOM),
    'decision' => 'warn' === $manifest['status'] ? 'hold' : 'proceed',
    'severity' => 'warn' === $manifest['status'] ? 'warning' : 'info',
    'reasons' => 'warn' === $manifest['status'] ? ['release_manifest_incomplete'] : ['release_manifest_green'],
    'actions' => 'warn' === $manifest['status'] ? ['repair_release_artifacts', 'rerun_release_manifest_generation'] : ['continue_release_candidate_validation'],
];
file_put_contents($root.'/build/release/release-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
file_put_contents($root.'/build/release/rollback-manifest.json', json_encode($rollback, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
file_put_contents($root.'/build/release/release-manifest.md', "# Release manifest\n\nGenerated successfully.\n");
file_put_contents($root.'/build/release/rollback-manifest.md', "# Rollback manifest\n\nGenerated successfully.\n");
