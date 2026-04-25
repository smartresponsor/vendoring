<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_category')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_category_vendor_category', columns: ['vendor_id', 'category_code'])]
final class VendorCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(name: 'category_code', type: 'string', length: 64)]
    private string $categoryCode;

    #[ORM\Column(name: 'category_name', type: 'string', length: 255, nullable: true)]
    private ?string $categoryName = null;

    #[ORM\Column(name: 'is_primary', type: 'boolean')]
    private bool $isPrimary = false;

    #[ORM\Column(name: 'assigned_at', type: 'datetime_immutable')]
    private DateTimeImmutable $assignedAt;

    public function __construct(Vendor $vendor, string $categoryCode, ?string $categoryName = null, bool $isPrimary = false)
    {
        $this->vendor = $vendor;
        $this->categoryCode = $categoryCode;
        $this->categoryName = $categoryName;
        $this->isPrimary = $isPrimary;
        $this->assignedAt = new DateTimeImmutable();
    }

    public function update(?string $categoryName = null, bool $isPrimary = false): void
    {
        $this->categoryName = $categoryName;
        $this->isPrimary = $isPrimary;
        $this->assignedAt = new DateTimeImmutable();
    }
}
