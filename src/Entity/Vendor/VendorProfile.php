<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

final class VendorProfile
{
    private ?int $id = null;
    private ?string $displayName = null;
    private ?string $about = null;
    private ?string $website = null;
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
}
