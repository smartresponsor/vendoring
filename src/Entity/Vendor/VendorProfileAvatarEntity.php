<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_profile_avatar')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_profile_avatar_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorProfileAvatarEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(VendorEntity $vendor, string $filePath)
    {
        $this->vendor = $vendor;
        $this->filePath = $filePath;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function update(string $filePath): void
    {
        $this->filePath = $filePath;
        $this->updatedAt = new DateTimeImmutable();
    }
}
