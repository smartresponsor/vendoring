<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Integration;

use App\Vendoring\Exception\Integration\VendorCrmProviderNotConfiguredException;
use App\Vendoring\Service\Integration\VendorNullCrmService;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;
use PHPUnit\Framework\TestCase;

final class VendorNullCrmServiceTest extends TestCase
{
    public function testImplementsCrmServiceInterface(): void
    {
        self::assertInstanceOf(VendorCrmServiceInterface::class, new VendorNullCrmService());
    }

    public function testRegisterVendorThrowsWhenNoProviderConfigured(): void
    {
        $vendor = $this->createMock(\App\Vendoring\Entity\Vendor\VendorEntity::class);

        $this->expectException(VendorCrmProviderNotConfiguredException::class);

        (new VendorNullCrmService())->registerVendor($vendor);
    }

    public function testExceptionMessageDescribesConfigurationAction(): void
    {
        $vendor = $this->createMock(\App\Vendoring\Entity\Vendor\VendorEntity::class);

        try {
            (new VendorNullCrmService())->registerVendor($vendor);
            self::fail('Expected VendorCrmProviderNotConfiguredException was not thrown.');
        } catch (VendorCrmProviderNotConfiguredException $exception) {
            self::assertStringContainsString('VendorCrmServiceInterface', $exception->getMessage());
        }
    }
}
