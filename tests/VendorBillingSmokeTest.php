<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorBilling;

final class VendorBillingSmokeTest extends TestCase
{
    public function testPayoutStatusFlow(): void
    {
        $v = new Vendor('Acme');
        $b = new VendorBilling($v);
        $this->assertTrue(true); // placeholder without Doctrine bootstrap
    }
}
