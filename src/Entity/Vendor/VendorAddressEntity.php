<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_address')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_address_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'country_code', type: 'string', length: 2, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $locality = null;

    #[ORM\Column(name: 'postal_code', type: 'string', length: 32, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(name: 'address_line_1', type: 'string', length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(name: 'address_line_2', type: 'string', length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(VendorEntity $vendor)
    {
        $this->vendor = $vendor;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function update(
        ?string $countryCode = null,
        ?string $region = null,
        ?string $locality = null,
        ?string $postalCode = null,
        ?string $addressLine1 = null,
        ?string $addressLine2 = null,
    ): void {
        $this->countryCode = $countryCode;
        $this->region = $region;
        $this->locality = $locality;
        $this->postalCode = $postalCode;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->updatedAt = new DateTimeImmutable();
    }
}
