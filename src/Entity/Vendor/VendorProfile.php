<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

final class VendorProfile
{
    private ?int $id = null;
    private ?string $displayName = null;
    private ?string $about = null;
    private ?string $website = null;
    /** @var array<string, string>|null */
    private ?array $socials = null;
    private ?string $seoTitle = null;
    private ?string $seoDescription = null;

    public function __construct(private readonly Vendor $vendor)
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /** @return array<string,string>|null */
    public function getSocials(): ?array
    {
        return $this->socials;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }
}
