<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

final class VendorAnalytics
{
    private ?int $id = null;

    /** @param array<string,mixed> $metrics */
    public function __construct(
        private readonly Vendor $vendor,
        private array $metrics = [],
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    /** @return array<string,mixed> */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
