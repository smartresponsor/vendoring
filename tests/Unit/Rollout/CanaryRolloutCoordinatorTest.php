<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Rollout;

use App\Vendoring\Service\Rollout\VendorCanaryRolloutCoordinatorService;
use App\Vendoring\ServiceInterface\Ops\VendorReleaseManifestBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ops\VendorRollbackDecisionEvaluatorServiceInterface;
use App\Vendoring\ServiceInterface\Rollout\VendorFeatureFlagServiceInterface;
use App\Vendoring\ServiceInterface\Rollout\VendorTrafficCohortResolverServiceInterface;
use PHPUnit\Framework\TestCase;

final class CanaryRolloutCoordinatorTest extends TestCase
{
    public function testDisabledFlagReturnsDisabledDecision(): void
    {
        $coordinator = new VendorCanaryRolloutCoordinatorService(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => false, 'cohort' => 'vendor:42', 'reason' => 'cohort_disabled', 'decision' => 'hold', 'severity' => 'warning', 'reasons' => [], 'actions' => []]),
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
        $coordinator = new VendorCanaryRolloutCoordinatorService(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'vendor:42', 'reason' => 'cohort_enabled', 'decision' => 'proceed', 'severity' => 'info', 'reasons' => [], 'actions' => []]),
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
        $coordinator = new VendorCanaryRolloutCoordinatorService(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'tenant:tenant-1', 'reason' => 'cohort_enabled', 'decision' => 'rollback', 'severity' => 'warning', 'reasons' => [], 'actions' => []]),
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
        $coordinator = new VendorCanaryRolloutCoordinatorService(
            $this->featureFlagService(['flag' => 'test_flag', 'enabled' => true, 'cohort' => 'tenant:tenant-1', 'reason' => 'cohort_enabled', 'decision' => 'hold', 'severity' => 'warning', 'reasons' => [], 'actions' => []]),
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
     * @param array{flag:string, enabled:bool, cohort:string, reason:string, decision?:string, severity?:string, reasons?:list<string>, actions?:list<string>} $decision
     */
    private function featureFlagService(array $decision): VendorFeatureFlagServiceInterface
    {
        return new class ($decision) implements VendorFeatureFlagServiceInterface {
            /** @param array{flag:string, enabled:bool, cohort:string, reason:string, decision?:string, severity?:string, reasons?:list<string>, actions?:list<string>} $decision */
            public function __construct(private readonly array $decision) {}
            public function isEnabled(string $flagName, ?string $tenantId = null, ?string $vendorId = null): bool
            {
                return $this->decision['enabled'];
            }
            public function explain(string $flagName, ?string $tenantId = null, ?string $vendorId = null): array
            {
                return [
                    'flag' => (string) $this->decision['flag'],
                    'enabled' => true === $this->decision['enabled'],
                    'cohort' => (string) $this->decision['cohort'],
                    'reason' => (string) $this->decision['reason'],
                ];
            }
        };
    }

    private function cohortResolver(string $cohort): VendorTrafficCohortResolverServiceInterface
    {
        return new class ($cohort) implements VendorTrafficCohortResolverServiceInterface {
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
    private function manifestBuilder(array $missingProbes): VendorReleaseManifestBuilderServiceInterface
    {
        return new class ($missingProbes) implements VendorReleaseManifestBuilderServiceInterface {
            /** @param list<string> $missingProbes */
            public function __construct(private readonly array $missingProbes) {}
            public function build(int $windowSeconds = 900): array
            {
                return [
                    'generatedAt' => '2026-03-31T10:00:00+00:00',
                    'windowSeconds' => $windowSeconds,
                    'releaseDocs' => [],
                    'buildArtifacts' => [],
                    'monitoring' => [
                        'status' => [] === $this->missingProbes ? 'green' : 'warn',
                        'alertCount' => 0,
                        'alertCodes' => [],
                        'openBreakers' => 0,
                        'missingProbes' => $this->missingProbes,
                    ],
                    'status' => [] === $this->missingProbes ? 'green' : 'warn',
                ];
            }
        };
    }

    /**
     * @param array<string,mixed> $decision
     */
    private function rollbackEvaluator(array $decision): VendorRollbackDecisionEvaluatorServiceInterface
    {
        return new class ($decision) implements VendorRollbackDecisionEvaluatorServiceInterface {
            /** @param array<string,mixed> $decision */
            public function __construct(private readonly array $decision) {}
            public function evaluate(array $manifest): array
            {
                $decisionValue = $this->decision['decision'] ?? 'hold';
                $severityValue = $this->decision['severity'] ?? 'warning';
                $reasonsValue = $this->decision['reasons'] ?? [];
                $actionsValue = $this->decision['actions'] ?? [];

                return [
                    'generatedAt' => '2026-03-31T10:00:00+00:00',
                    'decision' => is_string($decisionValue) ? $decisionValue : 'hold',
                    'severity' => is_string($severityValue) ? $severityValue : 'warning',
                    'reasons' => is_array($reasonsValue) ? array_values(array_filter($reasonsValue, 'is_string')) : [],
                    'actions' => is_array($actionsValue) ? array_values(array_filter($actionsValue, 'is_string')) : [],
                ];
            }
        };
    }
}
