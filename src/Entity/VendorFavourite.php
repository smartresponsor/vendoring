<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_favourite')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_favourite_vendor_target', columns: ['vendor_id', 'target_type', 'target_id'])]
final class VendorFavourite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(name: 'target_type', type: 'string', length: 64)]
    private string $targetType;

    #[ORM\Column(name: 'target_id', type: 'string', length: 128)]
    private string $targetId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(Vendor $vendor, string $targetType, string $targetId, ?string $note = null)
    {
        $this->vendor = $vendor;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->note = $note;
        $this->createdAt = new DateTimeImmutable();
    }

    public function update(?string $note = null): void
    {
        $this->note = $note;
    }
}
