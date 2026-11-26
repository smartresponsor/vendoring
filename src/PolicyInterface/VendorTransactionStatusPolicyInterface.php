<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface;

/**
 * Read-side policy contract for canonical transaction status handling.
 */
interface VendorTransactionStatusPolicyInterface
{
    /**
     * Normalize one status value into canonical lowercase representation.
     *
     * @param string $status raw transport-facing status value
     *
     * @return string canonical normalized status
     */
    public function normalize(string $status): string;

    /**
     * Determine whether one canonical transaction status may transition to another.
     *
     * @param string $fromStatus current transaction status
     * @param string $toStatus   requested target transaction status
     *
     * @return bool true when the transition is allowed by policy; false otherwise
     */
    public function canTransition(string $fromStatus, string $toStatus): bool;
}
