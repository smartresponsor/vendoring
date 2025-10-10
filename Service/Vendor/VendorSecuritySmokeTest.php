<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Service\Vendor\VendorSecurityService;
use App\Entity\Vendor\Vendor;
use App\Repository\Vendor\VendorApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;

final class SecuritySmokeTest extends TestCase
{
    public function testDummy(): void
    {
        $this->assertTrue(true);
    }
}
