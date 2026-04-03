<?php

declare(strict_types=1);

namespace App\ServiceInterface\Policy;

/**
 * Read-side validation and normalization contract for transaction amount input.
 */
interface VendorTransactionAmountPolicyInterface
{
    /**
     * Normalize one transport-facing transaction amount into canonical decimal string form.
     *
     * Implementations must reject blank, non-numeric, and non-positive values through
     * stable exception codes.
     *
     * @param string $amount Raw amount received from HTTP, CLI, or internal transport input.
     *
     * @return string Canonical amount formatted with two decimal places.
     */
    public function normalize(string $amount): string;
}
