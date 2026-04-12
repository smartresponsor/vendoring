<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorPassport
{
    private ?int $id = null;
    private bool $verified = false;
    private DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $taxId,
        private readonly string $country,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
