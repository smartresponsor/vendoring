<?php

declare(strict_types=1);

namespace App\Vendoring\Projection;

/**
 * Read-side ownership and assignment summary for a vendor.
 *
 * This projection keeps human-access semantics separate from API key access.
 */
final readonly class VendorOwnershipView
{
    /**
     * @param list<array{'userId': int, 'role': string, 'status': string, 'isPrimary': bool, 'grantedAt': string, 'revokedAt': ?string, 'capabilities': list<string>}> $assignments
     */
    public function __construct(
        private int $vendorId,
        private ?int $ownerUserId,
        private array $assignments,
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
     * @return array{'vendorId': int, 'ownerUserId': ?int, 'assignments': list<array{'userId': int, 'role': string, 'status': string, 'isPrimary': bool, 'grantedAt': string, 'revokedAt': ?string, 'capabilities': list<string>}>}
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
