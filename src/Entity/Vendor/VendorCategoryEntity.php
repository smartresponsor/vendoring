<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_category')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_category_vendor_category', columns: ['vendor_id', 'category_code'])]
#[ORM\Index(name: 'idx_vendor_category_vendor_primary', columns: ['vendor_id', 'is_primary'])]
final class VendorCategoryEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'category_code', type: 'string', length: 64)]
    private string $categoryCode;

    #[ORM\Column(name: 'category_name', type: 'string', length: 255, nullable: true)]
    private ?string $categoryName;

    #[ORM\Column(name: 'is_primary', type: 'boolean', options: ['default' => false])]
    private bool $isPrimary = false;

    #[ORM\Column(name: 'assigned_at', type: 'datetime_immutable')]
    private DateTimeImmutable $assignedAt;

    public function __construct(VendorEntity $vendor, string $categoryCode, ?string $categoryName = null, bool $isPrimary = false)
    {
        $this->vendor = $vendor;
        $this->categoryCode = trim($categoryCode);
        $this->categoryName = null !== $categoryName ? trim($categoryName) : null;
        $this->isPrimary = $isPrimary;
        $this->assignedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getCategoryCode(): string
    {
        return $this->categoryCode;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function getAssignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function update(?string $categoryName = null, ?bool $isPrimary = null): void
    {
        if (null !== $categoryName) {
            $this->categoryName = trim($categoryName);
        }

        if (null !== $isPrimary) {
            $this->isPrimary = $isPrimary;
        }
    }
}
