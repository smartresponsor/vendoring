<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * Read-side contract for building an operator-facing monitoring snapshot.
 *
 * Implementations aggregate exported logs, metrics, breaker state, and probe readiness
 * artifacts without mutating runtime state.
 */
interface VendorMonitoringSnapshotBuilderServiceInterface
{
    /**
     * Build a monitoring snapshot for the recent observability window.
     *
     * @param int $windowSeconds Lookback window in seconds for exported observability records.
     *
     * @return array{
     *   generatedAt:string,
     *   windowSeconds:int,
     *   logSummary:array{total:int,error:int,warning:int,routes:list<string>,errorCodes:list<string>},
     *   metricSummary:array{total:int,names:array<string,int>},
     *   breakerSummary:array{open:int,halfOpen:int,closed:int,scopes:list<string>},
     *   probeSummary:array{transaction:bool,finance:bool,payout:bool,postDeploy:bool},
     *   status:string
     * }
     */
    public function build(int $windowSeconds = 900): array;
}
