<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payout_accounts')]
#[ORM\UniqueConstraint(name: 'uniq_payout_account_tenant_vendor', columns: ['tenant_id', 'vendor_id'])]
final class VendorPayoutAccountEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 36)]
        public string $id,
        #[ORM\Column(name: 'tenant_id', type: 'string', length: 255)]
        public string $tenantId,
        #[ORM\Column(name: 'vendor_id', type: 'string', length: 255)]
        public string $vendorId,
        #[ORM\Column(type: 'string', length: 64)]
        public string $provider,
        #[ORM\Column(name: 'account_ref', type: 'string', length: 255)]
        public string $accountRef,
        #[ORM\Column(type: 'string', length: 8)]
        public string $currency,
        #[ORM\Column(type: 'boolean')]
        public bool $active,
        #[ORM\Column(name: 'created_at', type: 'string', length: 19)]
        public string $createdAt,
    ) {}
}
