<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Security;

use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\Vendoring\ValueObject\VendorRole;

/**
 * Canonical capability matrix for vendor-local human roles.
 *
 * The matrix intentionally fail-closes for unknown roles and unknown capabilities.
 */
final class VendorAuthorizationMatrix implements VendorAuthorizationMatrixInterface
{
    /**
     * @var array<string, list<string>>
     */
    private const array CAPABILITIES_BY_ROLE = [
        VendorRole::OWNER => [
            'transactions.read',
            'transactions.write',
            'payouts.read',
            'payouts.write',
            'statements.read',
            'statements.send',
            'ownership.read',
            'ownership.write',
        ],
        VendorRole::OPERATOR => [
            'transactions.read',
            'transactions.write',
            'statements.read',
            'ownership.read',
        ],
        VendorRole::FINANCE => [
            'transactions.read',
            'payouts.read',
            'payouts.write',
            'statements.read',
            'statements.send',
            'ownership.read',
        ],
        VendorRole::VIEWER => [
            'transactions.read',
            'payouts.read',
            'statements.read',
            'ownership.read',
        ],
    ];

    public function can(string $role, string $capability): bool
    {
        return in_array($capability, $this->capabilitiesForRole($role), true);
    }

    public function capabilitiesForRole(string $role): array
    {
        $normalizedRole = VendorRole::normalize($role);

        return self::CAPABILITIES_BY_ROLE[$normalizedRole] ?? [];
    }
}
