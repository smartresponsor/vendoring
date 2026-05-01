<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_wishlist_item')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_wishlist_item_target', columns: ['vendor_wishlist_id', 'target_type', 'target_id'])]
final class VendorWishlistItemEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorWishlistEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_wishlist_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorWishlistEntity $wishlist;

    #[ORM\Column(name: 'target_type', type: 'string', length: 64)]
    private string $targetType;

    #[ORM\Column(name: 'target_id', type: 'string', length: 128)]
    private string $targetId;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorWishlistEntity $wishlist, string $targetType, string $targetId, int $quantity = 1, ?string $note = null)
    {
        $this->wishlist = $wishlist;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->quantity = $quantity;
        $this->note = $note;
        $this->createdAt = new DateTimeImmutable();
    }

    public function update(int $quantity = 1, ?string $note = null): void
    {
        $this->quantity = $quantity;
        $this->note = $note;
    }
}
