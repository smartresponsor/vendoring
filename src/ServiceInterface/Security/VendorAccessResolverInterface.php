<?php

declare(strict_types=1);

namespace App\ServiceInterface\Security;

/**
 * Repository-backed access resolver for vendor-local human assignments.
 *
 * This contract resolves whether one user has a capability for one vendor based on active
 * assignments and the canonical authorization matrix.
 */
interface VendorAccessResolverInterface
{
    /**
     * Determine whether an active assignment grants the requested capability.
     *
     * @param int    $vendorId    Canonical numeric vendor identifier.
     * @param int    $userId      Canonical numeric user identifier from the human/operator side.
     * @param string $capability  Canonical capability name such as `ownership.read`.
     */
    public function canUserAccessVendorCapability(int $vendorId, int $userId, string $capability): bool;

    /**
     * Explain the access decision for diagnostics and operator-facing tooling.
     *
     * @param int    $vendorId    Canonical numeric vendor identifier.
     * @param int    $userId      Canonical numeric user identifier.
     * @param string $capability  Canonical capability name being checked.
     *
     * @return array{vendorId:int,userId:int,capability:string,granted:bool,roles:list<string>,reason:string}
     *         Stable explanation payload describing the evaluated roles and final decision.
     */
    public function explainUserAccessVendorCapability(int $vendorId, int $userId, string $capability): array;
}
