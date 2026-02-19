<?php
declare(strict_types = 1);

namespace App\Service\Kyc;

use SmartResponsor\Vendor\Port\Kyc\KycProviderPort;
use SmartResponsor\Vendor\Entity\Vendor\Passport;

final class KycService
{
    public function __construct(private KycProviderPort $provider)
    {
    }

    public function verify(Passport $p): bool
    {
        return $this->provider->verify($p->vendorId(), (string)$p->number());
    }
}
