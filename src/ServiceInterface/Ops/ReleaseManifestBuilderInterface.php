<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

/**
 * Read-side contract for assembling a release manifest from runtime monitoring, release artifacts,
 * and documentation presence checks.
 */
interface ReleaseManifestBuilderInterface
{
    /**
     * Build a release manifest for the current repository state.
     *
     * @param int $windowSeconds Monitoring lookback window used for snapshot and alert evaluation.
     *
     * @return array{
     *   generatedAt:string,
     *   windowSeconds:int,
     *   releaseDocs:array<string,bool>,
     *   buildArtifacts:array<string,bool>,
     *   monitoring:array{status:string,alertCount:int,alertCodes:list<string>,openBreakers:int,missingProbes:list<string>},
     *   status:string
     * }
     */
    public function build(int $windowSeconds = 900): array;
}
