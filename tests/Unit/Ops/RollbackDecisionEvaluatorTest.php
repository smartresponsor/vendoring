<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ops;

use App\Service\Ops\RollbackDecisionEvaluator;
use PHPUnit\Framework\TestCase;

final class RollbackDecisionEvaluatorTest extends TestCase
{
    public function testCriticalAlertForcesRollback(): void
    {
        $evaluator = new RollbackDecisionEvaluator();
        $decision = $evaluator->evaluate([
            'generatedAt' => date(DATE_ATOM),
            'windowSeconds' => 900,
            'releaseDocs' => ['rcBaseline' => true],
            'buildArtifacts' => ['rcEvidenceJson' => true],
            'monitoring' => [
                'status' => 'warn',
                'alertCount' => 1,
                'alertCodes' => ['outbound_circuit_open'],
                'openBreakers' => 1,
                'missingProbes' => [],
            ],
            'status' => 'warn',
        ]);

        self::assertSame('rollback', $decision['decision']);
        self::assertSame('critical', $decision['severity']);
    }

    public function testMissingArtifactsCauseHold(): void
    {
        $evaluator = new RollbackDecisionEvaluator();
        $decision = $evaluator->evaluate([
            'generatedAt' => date(DATE_ATOM),
            'windowSeconds' => 900,
            'releaseDocs' => ['rcBaseline' => false],
            'buildArtifacts' => ['rcEvidenceJson' => false],
            'monitoring' => [
                'status' => 'ok',
                'alertCount' => 0,
                'alertCodes' => [],
                'openBreakers' => 0,
                'missingProbes' => [],
            ],
            'status' => 'warn',
        ]);

        self::assertSame('hold', $decision['decision']);
        self::assertSame('warning', $decision['severity']);
    }
}
