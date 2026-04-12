<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorLedgerBinding
{
    private ?int $id = null;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $ledgerVendorId,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getLedgerVendorId(): string
    {
        return $this->ledgerVendorId;
    }
}
