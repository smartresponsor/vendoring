<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorAddressRepository::class)]
#[ORM\Table(name: 'vendor_address')]
class VendorAddressEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'address', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 2, nullable: true)] private ?string $countryCode = null;
    #[ORM\Column(type: 'string', length: 128, nullable: true)] private ?string $region = null;
    #[ORM\Column(type: 'string', length: 128, nullable: true)] private ?string $locality = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)] private ?string $postalCode = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $addressLine1 = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $addressLine2 = null;
    public function __construct(VendorEntity $vendor)
    {
        parent::__construct();
        $this->vendor = $vendor;
    }
}
