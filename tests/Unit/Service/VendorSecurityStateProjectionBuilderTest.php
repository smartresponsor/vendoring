<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Builder\Security\VendorSecurityStateProjectionBuilder;
use App\Vendoring\EntityInterface\VendorSecurityEntityInterface;
use PHPUnit\Framework\TestCase;

final class VendorSecurityStateProjectionBuilderTest extends TestCase
{
    public function testBuildProjectsVendorSecurityIntoArrayShape(): void
    {
        $security = $this->createMock(VendorSecurityEntityInterface::class);
        $security->expects(self::once())->method('getVendorId')->willReturn(101);
        $security->expects(self::once())->method('getStatus')->willReturn('enabled');

        $payload = (new VendorSecurityStateProjectionBuilder())->build($security)->toArray();

        self::assertSame([
            'vendorId' => 101,
            'status' => 'enabled',
        ], $payload);
    }

    public function testBuildSupportsMissingVendorIdDuringTransitionalSecurityState(): void
    {
        $security = $this->createMock(VendorSecurityEntityInterface::class);
        $security->expects(self::once())->method('getVendorId')->willReturn(null);
        $security->expects(self::once())->method('getStatus')->willReturn('pending');

        $payload = (new VendorSecurityStateProjectionBuilder())->build($security)->toArray();

        self::assertSame([
            'vendorId' => null,
            'status' => 'pending',
        ], $payload);
    }
}
