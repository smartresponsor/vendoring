<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorSecurityRepository::class)]
#[ORM\Table(name: 'vendor_security')]
class VendorSecurityEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'security', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    public function __construct(VendorEntity $vendor)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }
}
