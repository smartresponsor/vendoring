<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Policy;

use App\Vendoring\ServiceInterface\Policy\VendorTransactionStatusPolicyServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;

/**
 * Read-side policy for canonical transaction status normalization and transition rules.
 */
final class VendorTransactionStatusPolicyService implements VendorTransactionStatusPolicyServiceInterface
{
    /**
     * @var array<string, list<string>>
     */
    private const array ALLOWED_TRANSITIONS = [
        VendorTransactionStatusValueObject::PENDING => [VendorTransactionStatusValueObject::AUTHORIZED, VendorTransactionStatusValueObject::FAILED, VendorTransactionStatusValueObject::CANCELLED],
        VendorTransactionStatusValueObject::AUTHORIZED => [VendorTransactionStatusValueObject::SETTLED, VendorTransactionStatusValueObject::FAILED, VendorTransactionStatusValueObject::CANCELLED],
        VendorTransactionStatusValueObject::SETTLED => [VendorTransactionStatusValueObject::REFUNDED],
        VendorTransactionStatusValueObject::FAILED => [],
        VendorTransactionStatusValueObject::CANCELLED => [],
        VendorTransactionStatusValueObject::REFUNDED => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize(string $status): string
    {
        return strtolower(trim($status));
    }

    /**
     * Return the stable error code for missing status input.
     */
    public function requiredStatusErrorCode(): string
    {
        return VendorTransactionErrorCodeValueObject::STATUS_REQUIRED;
    }

    /**
     * Return the stable error code for invalid status transitions.
     */
    public function invalidTransitionErrorCode(): string
    {
        return VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION;
    }

    /**
     * {@inheritdoc}
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        $from = $this->normalize($fromStatus);
        $to = $this->normalize($toStatus);

        if ('' === $from || '' === $to) {
            return false;
        }

        if (!array_key_exists($from, self::ALLOWED_TRANSITIONS) || !array_key_exists($to, self::ALLOWED_TRANSITIONS)) {
            return false;
        }

        if ($from === $to) {
            return true;
        }

        return in_array($to, self::ALLOWED_TRANSITIONS[$from], true);
    }
}
