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

    public function testResolveFallsBackToExplicitTenantScope(): void
    {
        $resolver = new VendorTrafficCohortResolver();
        self::assertSame('tenant:tenant-a', $resolver->resolve(null, 'tenant-a'));
        self::assertSame('tenant:tenant-b', $resolver->resolve(null, 'tenant-b'));
        self::assertSame('vendor:42', $resolver->resolve('42', 'tenant-a'));
    }

    public function testResolveFallsBackToGlobalScope(): void
    {
        $resolver = new VendorTrafficCohortResolver();

        self::assertSame('global', $resolver->resolve(null));
    }
}
