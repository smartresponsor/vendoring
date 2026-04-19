<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAnalytics
{
    /** @var int|null */
    // @phpstan-ignore-next-line
    private ?int $id = null;

    /** @param array<string,mixed> $metrics */
    public function __construct(
        private readonly Vendor $vendor,
        private readonly array  $metrics = [],
    ) {}

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
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
