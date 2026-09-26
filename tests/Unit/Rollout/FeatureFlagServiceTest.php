<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Rollout;

use App\Vendoring\Resolver\Rollout\VendorTrafficCohortResolver;
use App\Vendoring\Service\Rollout\VendorFeatureFlagService;
use PHPUnit\Framework\TestCase;

final class FeatureFlagServiceTest extends TestCase
{
    public function testUndefinedFlagIsDisabled(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolver());

        self::assertFalse($service->isEnabled('missing_flag', '42'));
        self::assertSame('flag_not_defined', $service->explain('missing_flag', '42')['reason']);
    }

    public function testGloballyEnabledFlagHasStableExplanation(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolver(), [
            'new_operator_surface' => ['enabled' => true],
        ]);

        $decision = $service->explain('new_operator_surface', null);

        self::assertTrue($decision['enabled']);
        self::assertSame('global', $decision['cohort']);
        self::assertSame('globally_enabled', $decision['reason']);
    }

    public function testCohortFlagEnablesOnlyMatchingScope(): void
    {
        $service = new VendorFeatureFlagService(new VendorTrafficCohortResolver(), [
            'statement_canary' => [
                'enabled' => false,
                'cohorts' => ['vendor:42'],
            ],
        ]);

        self::assertTrue($service->isEnabled('statement_canary', '42'));
        self::assertFalse($service->isEnabled('statement_canary', '77'));
        self::assertSame('cohort_disabled', $service->explain('statement_canary', '77')['reason']);
    }
}
