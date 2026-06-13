<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorWishlistRepository::class)]
#[ORM\Table(name: 'vendor_wishlist')]
class VendorWishlistEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'wishlists')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $customerReference = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $nameEntity = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    public function __construct(VendorEntity $vendor, string $customerReference, string $nameEntity)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->customerReference = $customerReference;
        $this->nameEntity = $nameEntity;
        $this->status = 'active';
    }

    public function update(string $nameEntity, string $status): self
    {
        $this->nameEntity = $nameEntity;
        $this->status = $status;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getCustomerReference()
    {
        return $this->customerReference;
    }

    public function getName()
    {
        return $this->nameEntity;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
