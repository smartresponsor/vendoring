<?php

declare(strict_types=1);

namespace App\ServiceInterface\Observability;

/**
 * Read-side contract for evaluating monitoring snapshots into actionable alerts.
 */
interface AlertRuleEvaluatorInterface
{
    /**
     * Evaluate alert rules against one monitoring snapshot.
     *
     * @param array{
     *   generatedAt:string,
     *   windowSeconds:int,
     *   logSummary:array{total:int,error:int,warning:int,routes:list<string>,errorCodes:list<string>},
     *   metricSummary:array{total:int,names:array<string,int>},
     *   breakerSummary:array{open:int,halfOpen:int,closed:int,scopes:list<string>},
     *   probeSummary:array{transaction:bool,finance:bool,payout:bool,postDeploy:bool},
     *   status:string
     * } $snapshot
     *
     * @return list<array{code:string,severity:string,message:string,context:array<string,mixed>}>
     */
    public function evaluate(array $snapshot): array;
}
