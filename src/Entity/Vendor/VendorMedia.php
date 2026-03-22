<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

final class VendorMedia
{
    private ?int $id = null;
    private ?string $logoPath = null;
    private ?string $bannerPath = null;
    private ?array $gallery = null;

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
