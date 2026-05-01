<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorMediaRepository')]
#[ORM\Table(name: 'vendor_media')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_media_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorMediaEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'logo_path', type: 'string', length: 1024, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(name: 'banner_path', type: 'string', length: 1024, nullable: true)]
    private ?string $bannerPath = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $gallery = null;

    public function __construct(VendorEntity $vendor)
    {
        $this->vendor = $vendor;
    }

    /** @param list<string>|null $gallery */
    public function update(?string $logoPath = null, ?string $bannerPath = null, ?array $gallery = null): void
    {
        $this->logoPath = $logoPath;
        $this->bannerPath = $bannerPath;
        $this->gallery = $gallery;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function getBannerPath(): ?string
    {
        return $this->bannerPath;
    }

    /** @return list<string>|null */
    public function getGallery(): ?array
    {
        return $this->gallery;
    }
}
