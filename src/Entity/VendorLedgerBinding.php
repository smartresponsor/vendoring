<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorLedgerBinding
{
    /** @var int|null */
    private ?int $id = null;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $ledgerVendorId,
    ) {}

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
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
