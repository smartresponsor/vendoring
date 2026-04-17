<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Vendor;
use App\Service\VendorCrmService;
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
