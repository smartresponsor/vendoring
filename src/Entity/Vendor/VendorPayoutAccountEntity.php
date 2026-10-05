<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorPayoutAccountRepository::class)]
#[ORM\Table(
    name: 'vendor_payout_account',
    indexes: [
        new ORM\Index(name: 'idx_vendor_payout_account_vendor_id', columns: ['vendor_id']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_vendor_payout_account_business_id', columns: ['account_id']),
    ],
)]
class VendorPayoutAccountEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $accountId;
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 64)] public string $provider;
    #[ORM\Column(type: 'string', length: 128)] public string $accountRef;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    #[ORM\Column(name: 'account_active', type: 'boolean')] public bool $active = true;
    public function __construct(
        string $id,
        string $vendorId,
        string $provider,
        string $accountRef,
        string $currency,
        bool $active = true,
        mixed $createdAt = null,
    ) {
        parent::__construct('active');
        $this->accountId = $id;
        unset($createdAt);
        $this->vendorId = $vendorId;
        $this->provider = $provider;
        $this->accountRef = $accountRef;
        $this->currency = $currency;
        $this->active = $active;
    }
}
