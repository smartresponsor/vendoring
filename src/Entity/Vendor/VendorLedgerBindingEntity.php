<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorLedgerBindingRepository::class)]
#[ORM\Table(name: 'vendor_ledger_binding')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_ledger_binding_tenant_vendor', columns: ['tenant_id', 'ledger_vendor_id'])]
class VendorLedgerBindingEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(targetEntity: VendorEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] private ?string $tenantId = null;
    #[ORM\Column(type: 'string', length: 64)] private string $ledgerVendorId;
    public function __construct(VendorEntity $vendor, string $tenantId, string $ledgerVendorId)
    {
        if ('' === trim($tenantId) || '' === trim($ledgerVendorId)) {
            throw new \InvalidArgumentException('Tenant and ledger vendor scope required');
        }
        parent::__construct();
        $this->tenantId = $tenantId;
        $this->vendor = $vendor;
        $this->ledgerVendorId = $ledgerVendorId;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getLedgerVendorId(): string
    {
        return $this->ledgerVendorId;
    }
}
