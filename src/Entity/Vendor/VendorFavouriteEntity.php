<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorFavouriteRepository::class)]
#[ORM\Table(name: 'vendor_favourite')]
class VendorFavouriteEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'favourites')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $targetType = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $targetId = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $note = null;
    public function __construct(VendorEntity $vendor, string $targetType, string $targetId, ?string $note = null)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->note = $note;
    }

    public function update(?string $note = null): self
    {
        $this->note = $note;

        return $this;
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getTargetType()
    {
        return $this->targetType;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function getNote()
    {
        return $this->note;
    }
}
