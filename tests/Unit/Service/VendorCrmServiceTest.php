<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Service\Integration\VendorCrmService;
use PHPUnit\Framework\TestCase;

final class VendorCrmServiceTest extends TestCase
{
    public function testRegisterVendorIsIntentionalNoOpUntilProviderIsConfigured(): void
    {
        $vendor = new Vendor('Vendor Example');

        $service = new VendorCrmService();
        $service->registerVendor($vendor);

        self::expectNotToPerformAssertions();
    }
}
