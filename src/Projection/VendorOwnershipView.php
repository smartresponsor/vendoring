<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Read-side ownership and assignment summary for a vendor.
 *
 * This projection keeps human-access semantics separate from API key access.
 */
final class VendorOwnershipView
{
    /**
     * @param list<array<string, mixed>> $assignments
     */
    public function __construct(
        private readonly int $vendorId,
        private readonly ?int $ownerUserId,
        private readonly array $assignments,
    ) {
    }

    public function getVendorId(): int
    {
        return $this->vendorId;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    /**
     * @return array{vendorId:int, ownerUserId:?int, assignments:list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'ownerUserId' => $this->ownerUserId,
            'assignments' => $this->assignments,
        ];
    }
}
