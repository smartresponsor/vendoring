<?php

declare(strict_types=1);

namespace App\Service\Policy;

use App\ServiceInterface\Policy\VendorTransactionStatusPolicyInterface;
use App\ValueObject\VendorTransactionErrorCode;
use App\ValueObject\VendorTransactionStatus;

final class VendorTransactionStatusPolicy implements VendorTransactionStatusPolicyInterface
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        VendorTransactionStatus::PENDING => [VendorTransactionStatus::AUTHORIZED, VendorTransactionStatus::FAILED, VendorTransactionStatus::CANCELLED],
        VendorTransactionStatus::AUTHORIZED => [VendorTransactionStatus::SETTLED, VendorTransactionStatus::FAILED, VendorTransactionStatus::CANCELLED],
        VendorTransactionStatus::SETTLED => [VendorTransactionStatus::REFUNDED],
        VendorTransactionStatus::FAILED => [],
        VendorTransactionStatus::CANCELLED => [],
        VendorTransactionStatus::REFUNDED => [],
    ];

    public function normalize(string $status): string
    {
        return strtolower(trim($status));
    }

    public function requiredStatusErrorCode(): string
    {
        return VendorTransactionErrorCode::STATUS_REQUIRED;
    }

    public function invalidTransitionErrorCode(): string
    {
        return VendorTransactionErrorCode::INVALID_STATUS_TRANSITION;
    }

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
