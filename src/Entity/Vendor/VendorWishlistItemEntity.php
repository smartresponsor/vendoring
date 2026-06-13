<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorWishlistItemRepository::class)]
#[ORM\Table(name: 'vendor_wishlist_item')]
class VendorWishlistItemEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorWishlistEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorWishlistEntity $wishlist;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $targetType = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $targetId = '';
    #[ORM\Column(type: 'integer')] private int $quantity = 0;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $note = null;
    public function __construct(VendorWishlistEntity $wishlist, string $targetType, string $targetId, int $quantity, ?string $note = null)
    {
        parent::__construct();
        $this->wishlist = $wishlist;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->quantity = $quantity;
        $this->note = $note;
    }

    public function update(int $quantity, ?string $note = null): self
    {
        $this->quantity = $quantity;
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

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getNote()
    {
        return $this->note;
    }
}
