<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorLedgerBindingRepository::class)]
#[ORM\Table(name: 'vendor_ledger_binding')]
class VendorLedgerBindingEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(targetEntity: VendorEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64, unique: true)] private string $ledgerVendorId;
    public function __construct(VendorEntity $vendor, string $ledgerVendorId)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->ledgerVendorId = $ledgerVendorId;
    }
}
