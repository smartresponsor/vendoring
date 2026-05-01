<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Policy;

/**
 * Read-side policy contract for canonical transaction status handling.
 */
interface VendorTransactionStatusPolicyServiceInterface
{
    /**
     * Normalize one status value into canonical lowercase representation.
     *
     * @param string $status Raw transport-facing status value.
     *
     * @return string Canonical normalized status.
     */
    public function normalize(string $status): string;

    /**
     * Determine whether one canonical transaction status may transition to another.
     *
     * @param string $fromStatus Current transaction status.
     * @param string $toStatus   Requested target transaction status.
     *
     * @return bool True when the transition is allowed by policy; false otherwise.
     */
    public function canTransition(string $fromStatus, string $toStatus): bool;
}
