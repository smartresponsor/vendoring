<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_wishlist')]
#[ORM\Index(name: 'idx_vendor_wishlist_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorWishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(name: 'customer_reference', type: 'string', length: 128)]
    private string $customerReference;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(Vendor $vendor, string $customerReference, string $name)
    {
        $this->vendor = $vendor;
        $this->customerReference = $customerReference;
        $this->name = $name;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function update(string $name, string $status = 'active'): void
    {
        $this->name = $name;
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }
}
