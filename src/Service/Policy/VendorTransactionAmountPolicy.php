<?php

declare(strict_types=1);

namespace App\Service\Policy;

use App\ServiceInterface\Policy\VendorTransactionAmountPolicyInterface;
use App\ValueObject\VendorTransactionErrorCode;
use InvalidArgumentException;

/**
 * Read-side policy that validates and normalizes transport-facing transaction amounts.
 */
final class VendorTransactionAmountPolicy implements VendorTransactionAmountPolicyInterface
{
    /**
     * Normalize one transaction amount into canonical decimal-string form.
     *
     * Stable validation error codes include:
     * - amount_required
     * - amount_not_numeric
     * - amount_not_positive
     *
     * @throws InvalidArgumentException When the amount cannot be normalized under policy rules.
     */
    public function normalize(string $amount): string
    {
        $normalized = trim($amount);

        if ('' === $normalized) {
            throw new InvalidArgumentException(VendorTransactionErrorCode::AMOUNT_REQUIRED);
        }

        if (!is_numeric($normalized)) {
            throw new InvalidArgumentException(VendorTransactionErrorCode::AMOUNT_NOT_NUMERIC);
        }

        $value = round((float) $normalized, 2);

        if ($value <= 0.0) {
            throw new InvalidArgumentException(VendorTransactionErrorCode::AMOUNT_NOT_POSITIVE);
        }

        return number_format($value, 2, '.', '');
    }
}
