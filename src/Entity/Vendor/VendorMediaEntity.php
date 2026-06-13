<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorMediaRepository::class)]
#[ORM\Table(name: 'vendor_media')]
class VendorMediaEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'media', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 1024, nullable: true)] private ?string $logoPath = null;
    #[ORM\Column(type: 'string', length: 1024, nullable: true)] private ?string $bannerPath = null;
    #[ORM\Column(type: 'json', nullable: true)] private ?array $gallery = null;
    public function __construct(VendorEntity $vendor)
    {
        parent::__construct();
        $this->vendor = $vendor;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function update(?string $logoPath, ?string $bannerPath, ?array $gallery): self
    {
        $this->logoPath = $logoPath;
        $this->bannerPath = $bannerPath;
        $this->gallery = $gallery;
        $this->touchObject();

        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function getBannerPath(): ?string
    {
        return $this->bannerPath;
    }

    public function getGallery(): ?array
    {
        return $this->gallery;
    }
}
