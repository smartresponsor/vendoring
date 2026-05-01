<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorPassportRepository')]
#[ORM\Table(name: 'vendor_passport')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_passport_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorPassportEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private readonly VendorEntity $vendor;

    #[ORM\Column(name: 'tax_id', type: 'string', length: 64)]
    private readonly string $taxId;

    #[ORM\Column(type: 'string', length: 8)]
    private readonly string $country;

    #[ORM\Column(type: 'boolean')]
    private bool $verified = false;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorEntity $vendor, string $taxId, string $country)
    {
        $this->vendor = $vendor;
        $this->taxId = $taxId;
        $this->country = $country;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getTaxId(): string
    {
        return $this->taxId;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function markVerified(): void
    {
        $this->verified = true;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
