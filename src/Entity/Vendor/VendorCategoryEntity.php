<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCategoryRepository::class)]
#[ORM\Table(name: 'vendor_category')]
class VendorCategoryEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'categories')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $categoryCode = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $categoryName = null;
    #[ORM\Column(type: 'boolean')] private bool $isPrimary = false;
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $assignedAt = null;
    public function __construct(VendorEntity $vendor, string $categoryCode, ?string $categoryName = null, bool $isPrimary = false)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->categoryCode = $categoryCode;
        $this->categoryName = $categoryName;
        $this->isPrimary = $isPrimary;
        $this->assignedAt = new \DateTimeImmutable();
    }

    public function update(?string $categoryName = null, ?bool $isPrimary = null): self
    {
        if (null !== $categoryName) {
            $this->categoryName = $categoryName;
        } if (null !== $isPrimary) {
            $this->isPrimary = $isPrimary;
        }

return $this;
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getCategoryCode()
    {
        return $this->categoryCode;
    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function isIsPrimary()
    {
        return $this->isPrimary;
    }

    public function getAssignedAt()
    {
        return $this->assignedAt;
    }
}
