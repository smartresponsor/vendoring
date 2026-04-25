<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * @param array<string, mixed> $meta
 */
final readonly class VendorGroupUpsertDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public int $vendorId,
        public string $code,
        public string $name,
        public string $status = 'active',
        public array $meta = [],
    ) {}
}
