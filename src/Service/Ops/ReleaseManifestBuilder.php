<?php

declare(strict_types=1);

namespace App\Service\Ops;

use App\ServiceInterface\Observability\AlertRuleEvaluatorInterface;
use App\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use DateTimeImmutable;

/**
 * Read-side builder for release and rollback operators.
 *
 * The builder combines runtime monitoring status, evaluated alerts, documentation presence, and
 * generated build artifacts into a deterministic manifest for release decisions.
 */
final readonly class ReleaseManifestBuilder implements ReleaseManifestBuilderInterface
{
    public function __construct(
        private MonitoringSnapshotBuilderInterface $snapshotBuilder,
        private AlertRuleEvaluatorInterface        $alertRuleEvaluator,
        private string                             $projectDir,
    ) {}

    public function build(int $windowSeconds = 900): array
    {
        $snapshot = $this->snapshotBuilder->build($windowSeconds);
        $alerts = $this->alertRuleEvaluator->evaluate($snapshot);

        $releaseDocs = $this->releaseDocs();
        $buildArtifacts = $this->buildArtifacts();
        $alertCodes = [];
        foreach ($alerts as $alert) {
            $code = $alert['code'];
            if (is_string($code) && '' !== $code) {
                $alertCodes[] = $code;
            }
        }

        $missingProbes = [];
        foreach ($snapshot['probeSummary'] as $probe => $present) {
            if (false === $present) {
                $missingProbes[] = (string) $probe;
            }
        }

        $status = 'ok';
        if (in_array(false, $releaseDocs, true) || in_array(false, $buildArtifacts, true) || 'warn' === $snapshot['status']) {
            $status = 'warn';
        }

        return [
            'generatedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
            'windowSeconds' => max(1, $windowSeconds),
            'releaseDocs' => $releaseDocs,
            'buildArtifacts' => $buildArtifacts,
            'monitoring' => [
                'status' => $snapshot['status'],
                'alertCount' => count($alerts),
                'alertCodes' => array_values(array_unique($alertCodes)),
                'openBreakers' => $snapshot['breakerSummary']['open'],
                'missingProbes' => $missingProbes,
            ],
            'status' => $status,
        ];
    }

    /**
     * @return array<string,bool>
     */
    private function releaseDocs(): array
    {
        return [
            'rcBaseline' => is_file($this->projectDir . '/docs/release/RC_BASELINE.md'),
            'rcRuntimeSurfaces' => is_file($this->projectDir . '/docs/release/RC_RUNTIME_SURFACES.md'),
            'rcOperatorSurface' => is_file($this->projectDir . '/docs/release/RC_OPERATOR_SURFACE.md'),
            'rcEvidencePack' => is_file($this->projectDir . '/docs/release/RC_EVIDENCE_PACK.md'),
            'rcRollbackManifest' => is_file($this->projectDir . '/docs/release/RC_ROLLBACK_MANIFEST.md'),
            'rcReleaseManifest' => is_file($this->projectDir . '/docs/release/RC_RELEASE_MANIFEST.md'),
        ];
    }

    /**
     * @return array<string,bool>
     */
    private function buildArtifacts(): array
    {
        return [
            'rcEvidenceJson' => is_file($this->projectDir . '/build/release/rc-evidence.json'),
            'rcEvidenceMd' => is_file($this->projectDir . '/build/release/rc-evidence.md'),
            'phpdocumentorIndex' => is_file($this->projectDir . '/build/docs/phpdocumentor/index.html'),
            'releaseManifestJson' => is_file($this->projectDir . '/build/release/release-manifest.json'),
            'rollbackManifestJson' => is_file($this->projectDir . '/build/release/rollback-manifest.json'),
        ];
    }
}
