<?php

declare(strict_types=1);

namespace App\Tests\Unit\Rollout;

use App\Service\Rollout\CanaryRolloutCoordinator;
use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
use App\ServiceInterface\Rollout\FeatureFlagServiceInterface;
use App\ServiceInterface\Rollout\TrafficCohortResolverInterface;
use PHPUnit\Framework\TestCase;

final class CanaryRolloutCoordinatorTest extends TestCase
{
    public function testDisabledFlagReturnsDisabledDecision(): void
    {
        $coordinator = new CanaryRolloutCoordinator(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => false, 'cohort' => 'vendor:42', 'reason' => 'cohort_disabled']),
            $this->cohortResolver('vendor:42'),
            $this->manifestBuilder([]),
            $this->rollbackEvaluator(['decision' => 'proceed']),
        );

        $report = $coordinator->evaluate('test_flag', 'tenant-1', '42');

        self::assertSame('disabled', $report['canary']['decision']);
        self::assertSame('keep_flag_disabled', $report['canary']['recommendedAction']);
    }

    public function testProceedingVendorCanarySuggestsTenantExpansion(): void
    {
        $coordinator = new CanaryRolloutCoordinator(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'vendor:42', 'reason' => 'cohort_enabled']),
            $this->cohortResolver('vendor:42'),
            $this->manifestBuilder([]),
            $this->rollbackEvaluator(['decision' => 'proceed']),
        );

        $report = $coordinator->evaluate('test_flag', 'tenant-1', '42');

        self::assertSame('proceed', $report['canary']['decision']);
        self::assertSame('expand_canary_scope', $report['canary']['recommendedAction']);
        self::assertSame('tenant:tenant-1', $report['canary']['nextCohort']);
    }

    public function testRollbackDecisionWinsOverEnabledFlag(): void
    {
        $coordinator = new CanaryRolloutCoordinator(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'tenant:tenant-1', 'reason' => 'cohort_enabled']),
            $this->cohortResolver('tenant:tenant-1'),
            $this->manifestBuilder([]),
            $this->rollbackEvaluator(['decision' => 'rollback']),
        );

        $report = $coordinator->evaluate('test_flag', 'tenant-1', null);

        self::assertSame('rollback', $report['canary']['decision']);
        self::assertSame('disable_flag_for_current_cohort', $report['canary']['recommendedAction']);
    }

    public function testMissingProbeForcesHold(): void
    {
        $coordinator = new CanaryRolloutCoordinator(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'tenant:tenant-1', 'reason' => 'cohort_enabled']),
            $this->cohortResolver('tenant:tenant-1'),
            $this->manifestBuilder(['transaction']),
            $this->rollbackEvaluator(['decision' => 'proceed']),
        );

        $report = $coordinator->evaluate('test_flag', 'tenant-1', null);

        self::assertSame('hold', $report['canary']['decision']);
        self::assertSame('required_probe_missing', $report['canary']['reason']);
        self::assertFalse($report['canary']['probeGate']['transaction']);
    }

    /**
     * @param array{flag:string, enabled:bool, cohort:string, reason:string} $decision
     */
    private function featureFlagService(array $decision): FeatureFlagServiceInterface
    {
        return new class ($decision) implements FeatureFlagServiceInterface {
            /** @param array{flag:string, enabled:bool, cohort:string, reason:string} $decision */
            public function __construct(private readonly array $decision) {}
            public function isEnabled(string $flagName, ?string $tenantId = null, ?string $vendorId = null): bool
            {
                return $this->decision['enabled'];
            }
            public function explain(string $flagName, ?string $tenantId = null, ?string $vendorId = null): array
            {
                return $this->decision;
            }
        };
    }

    private function cohortResolver(string $cohort): TrafficCohortResolverInterface
    {
        return new class ($cohort) implements TrafficCohortResolverInterface {
            public function __construct(private readonly string $cohort) {}
            public function resolve(?string $tenantId = null, ?string $vendorId = null): string
            {
                return $this->cohort;
            }
        };
    }

    /**
     * @param list<string> $missingProbes
     */
    private function manifestBuilder(array $missingProbes): ReleaseManifestBuilderInterface
    {
        return new class ($missingProbes) implements ReleaseManifestBuilderInterface {
            /** @param list<string> $missingProbes */
            public function __construct(private readonly array $missingProbes) {}
            public function build(int $windowSeconds = 900): array
            {
                return [
                    'monitoring' => [
                        'missingProbes' => $this->missingProbes,
                    ],
                ];
            }
        };
    }

    /**
     * @param array<string,mixed> $decision
     */
    private function rollbackEvaluator(array $decision): RollbackDecisionEvaluatorInterface
    {
        return new class ($decision) implements RollbackDecisionEvaluatorInterface {
            /** @param array<string,mixed> $decision */
            public function __construct(private readonly array $decision) {}
            public function evaluate(array $manifest): array
            {
                return $this->decision;
            }
        };
    }
}
