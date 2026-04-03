<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

/**
 * Read-side contract for converting a release manifest into an operational rollback decision.
 */
interface RollbackDecisionEvaluatorInterface
{
    /**
     * Evaluate whether the current release state should proceed, hold, or roll back.
     *
     * @param array{
     *   generatedAt:string,
     *   windowSeconds:int,
     *   releaseDocs:array<string,bool>,
     *   buildArtifacts:array<string,bool>,
     *   monitoring:array{status:string,alertCount:int,alertCodes:list<string>,openBreakers:int,missingProbes:list<string>},
     *   status:string
     * } $manifest Release manifest as returned by the release manifest builder.
     *
     * @return array{generatedAt:string,decision:string,severity:string,reasons:list<string>,actions:list<string>}
     */
    public function evaluate(array $manifest): array;
}
