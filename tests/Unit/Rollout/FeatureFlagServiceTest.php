<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Rollout;

use App\Vendoring\Service\Rollout\VendorFeatureFlagService;
use App\Vendoring\Service\Rollout\VendorTrafficCohortResolverService;
use PHPUnit\Framework\TestCase;

final class FeatureFlagServiceTest extends TestCase
{
    public function testUndefinedFlagIsDisabled(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolverService());

        self::assertFalse($service->isEnabled('missing_flag', 'tenant-1', '42'));
        self::assertSame('flag_not_defined', $service->explain('missing_flag', 'tenant-1', '42')['reason']);
    }

    public function testGloballyEnabledFlagHasStableExplanation(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolverService(), [
            'new_operator_surface' => ['enabled' => true],
        ]);

        $decision = $service->explain('new_operator_surface', null, null);

        self::assertTrue($decision['enabled']);
        self::assertSame('global', $decision['cohort']);
        self::assertSame('globally_enabled', $decision['reason']);
    }

    public function testCohortFlagEnablesOnlyMatchingScope(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolverService(), [
            'statement_canary' => [
                'enabled' => false,
                'cohorts' => ['tenant:tenant-1', 'vendor:42'],
            ],
        ]);

        self::assertTrue($service->isEnabled('statement_canary', 'tenant-1', null));
        self::assertTrue($service->isEnabled('statement_canary', 'tenant-x', '42'));
        self::assertFalse($service->isEnabled('statement_canary', 'tenant-x', '77'));
        self::assertSame('cohort_disabled', $service->explain('statement_canary', 'tenant-x', '77')['reason']);
    }
}
