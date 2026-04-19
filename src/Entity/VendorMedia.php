<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorMedia
{
    /** @var int|null */
    // @phpstan-ignore-next-line
    private ?int $id = null;
    private ?string $logoPath = null;
    private ?string $bannerPath = null;
    /** @var list<string>|null */
    private ?array $gallery = null;

    public function __construct(private readonly Vendor $vendor) {}

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

    public function getVendor(): Vendor
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
