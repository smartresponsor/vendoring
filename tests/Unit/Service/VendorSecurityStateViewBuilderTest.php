<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\EntityInterface\VendorSecurityInterface;
use App\Vendoring\Service\VendorSecurityStateViewBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorSecurityStateViewBuilderTest extends TestCase
{
    public function testBuildProjectsVendorSecurityIntoArrayShape(): void
    {
        $security = $this->createMock(VendorSecurityInterface::class);
        $security->expects(self::once())->method('getVendorId')->willReturn(101);
        $security->expects(self::once())->method('getStatus')->willReturn('enabled');

        $payload = (new VendorSecurityStateViewBuilder())->build($security)->toArray();

        self::assertSame([
            'vendorId' => 101,
            'status' => 'enabled',
        ], $payload);
    }

    public function testBuildSupportsMissingVendorIdDuringTransitionalSecurityState(): void
    {
        $security = $this->createMock(VendorSecurityInterface::class);
        $security->expects(self::once())->method('getVendorId')->willReturn(null);
        $security->expects(self::once())->method('getStatus')->willReturn('pending');

        $payload = (new VendorSecurityStateViewBuilder())->build($security)->toArray();

        self::assertSame([
            'vendorId' => null,
            'status' => 'pending',
        ], $payload);
    }
}
