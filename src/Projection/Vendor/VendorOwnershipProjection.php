<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Read-side ownership and assignment summary for a vendor.
 *
 * This projection keeps human-access semantics separate from API key access.
 */
final readonly class VendorOwnershipProjection
{
    /**
     * @param list<array{'userId': int, 'role': string, 'status': string, 'isPrimary': bool, 'grantedAt': string, 'revokedAt': ?string, 'capabilities': list<string>}> $assignments
     * @param array<string, int> $relationCounts
     */
    public function __construct(
        private int $vendorId,
        private ?int $ownerUserId,
        private array $assignments,
        private array $relationCounts = [],
    ) {}

    public function getVendorId(): int
    {
        return $this->vendorId;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    /**
     * @return list<array{'userId': int, 'role': string, 'status': string, 'isPrimary': bool, 'grantedAt': string, 'revokedAt': ?string, 'capabilities': list<string>}>
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    /**
     * @return array<string, int>
     */
    public function getRelationCounts(): array
    {
        return $this->relationCounts;
    }

    /**
     * @return array{'vendorId': int, 'ownerUserId': ?int, 'assignments': list<array{'userId': int, 'role': string, 'status': string, 'isPrimary': bool, 'grantedAt': string, 'revokedAt': ?string, 'capabilities': list<string>}>, 'relationCounts': array<string, int>}
     */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'ownerUserId' => $this->ownerUserId,
            'assignments' => $this->assignments,
            'relationCounts' => $this->relationCounts,
        ];
    }
}
