<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorProfileCoverRepository::class)]
#[ORM\Table(name: 'vendor_profile_cover')]
class VendorProfileCoverEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(targetEntity: VendorEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 1024)] private string $filePath;
    public function __construct(VendorEntity $vendor, string $filePath)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->filePath = $filePath;
    }

    public function update(string $filePath): self
    {
        $this->filePath = $filePath;
        $this->touchModified();

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }
}
