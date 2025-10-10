<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Entity\Vendor\Vendor;

final class BasicSmokeTest extends TestCase
{
    public function testVendorActivation(): void
    {
        $vendor = new Vendor('Acme', 1);
        $this->assertSame('inactive', $vendor->getStatus());
        $vendor->activate();
        $this->assertSame('active', $vendor->getStatus());
    }
}
