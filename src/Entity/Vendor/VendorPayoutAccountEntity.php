<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorPayoutAccountRepository::class)]
#[ORM\Table(name: 'vendor_payout_account')]
class VendorPayoutAccountEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $tenantId;
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 64)] public string $provider;
    #[ORM\Column(type: 'string', length: 128)] public string $accountRef;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    #[ORM\Column(type: 'boolean')] public bool $active = true;
    public function __construct(string $tenantId, string $vendorId, string $provider, string $accountRef, string $currency)
    {
        parent::__construct('active');
        $this->tenantId = $tenantId;
        $this->vendorId = $vendorId;
        $this->provider = $provider;
        $this->accountRef = $accountRef;
        $this->currency = $currency;
    }
}
