<?php

declare(strict_types=1);

namespace App\ServiceInterface\Security;

/**
 * Read-side authorization matrix for vendor-local human roles.
 *
 * This contract converts canonical roles into capability decisions without reading runtime
 * identity sources. Repository-backed actor resolution belongs to dedicated access resolvers.
 */
interface VendorAuthorizationMatrixInterface
{
    /**
     * Determine whether one canonical role grants a capability.
     *
     * @param string $role       Canonical vendor-local role, such as `owner` or `finance`.
     * @param string $capability Canonical capability name, such as `transactions.write`.
     */
    public function can(string $role, string $capability): bool;

    /**
     * List the canonical capabilities granted by one canonical role.
     *
     * @param string $role Canonical vendor-local role.
     *
     * @return list<string> Deterministic capability list for documentation, UI, and tests.
     */
    public function capabilitiesForRole(string $role): array;
}
