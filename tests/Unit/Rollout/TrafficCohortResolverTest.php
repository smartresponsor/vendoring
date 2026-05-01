<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Rollout;

use App\Vendoring\Service\Rollout\VendorTrafficCohortResolverService;
use PHPUnit\Framework\TestCase;

final class TrafficCohortResolverTest extends TestCase
{
    public function testResolvePrefersVendorScopeWhenAvailable(): void
    {
        $resolver = new VendorTrafficCohortResolverService();

        self::assertSame('vendor:42', $resolver->resolve('tenant-1', '42'));
    }

    public function testResolveFallsBackToTenantScope(): void
    {
        $resolver = new VendorTrafficCohortResolverService();

        self::assertSame('tenant:tenant-1', $resolver->resolve('tenant-1', null));
    }

    public function testResolveFallsBackToGlobalScope(): void
    {
        $resolver = new VendorTrafficCohortResolverService();

        self::assertSame('global', $resolver->resolve(null, null));
    }
}
