<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Rollout;

use App\Vendoring\Resolver\Rollout\VendorTrafficCohortResolver;
use PHPUnit\Framework\TestCase;

final class TrafficCohortResolverTest extends TestCase
{
    public function testResolvePrefersVendorScopeWhenAvailable(): void
    {
        $resolver = new VendorTrafficCohortResolver();

        self::assertSame('vendor:42', $resolver->resolve('42'));
    }

    public function testResolveFallsBackToGlobalScope(): void
    {
        $resolver = new VendorTrafficCohortResolver();

        self::assertSame('global', $resolver->resolve(null));
    }
}
