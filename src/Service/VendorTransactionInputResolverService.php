<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\ServiceInterface\VendorTransactionInputResolverServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionData;
use App\Vendoring\ValueObject\VendorTransactionErrorCode;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionInputResolverService implements VendorTransactionInputResolverServiceInterface
{
    public function resolveCreateData(Request $request): VendorTransactionData
    {
        $payload = $request->toArray();

        foreach ([
            'vendorId' => VendorTransactionErrorCode::VENDOR_ID_REQUIRED,
            'orderId' => VendorTransactionErrorCode::ORDER_ID_REQUIRED,
            'amount' => VendorTransactionErrorCode::AMOUNT_REQUIRED,
        ] as $field => $errorCode) {
            $normalized = self::trimmedScalar($payload[$field] ?? null);

            if ('' === $normalized) {
                throw new InvalidArgumentException($errorCode);
            }
        }

        $projectId = null;
        if (array_key_exists('projectId', $payload) && null !== $payload['projectId']) {
            $normalizedProjectId = self::trimmedScalar($payload['projectId']);
            $projectId = '' === $normalizedProjectId ? null : $normalizedProjectId;
        }

        return new VendorTransactionData(
            vendorId: self::trimmedScalar($payload['vendorId'] ?? null),
            orderId: self::trimmedScalar($payload['orderId'] ?? null),
            projectId: $projectId,
            amount: self::trimmedScalar($payload['amount'] ?? null),
        );
    }

    public function resolveStatus(Request $request): string
    {
        $payload = $request->toArray();
        $status = self::trimmedScalar($payload['status'] ?? null);

        if ('' === $status) {
            throw new InvalidArgumentException(VendorTransactionErrorCode::STATUS_REQUIRED);
        }

        return $status;
    }

    private static function trimmedScalar(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    public function normalizeErrorCode(string $message): string
    {
        return match ($message) {
            VendorTransactionErrorCode::DUPLICATE_TRANSACTION,
            VendorTransactionErrorCode::VENDOR_ID_REQUIRED,
            VendorTransactionErrorCode::ORDER_ID_REQUIRED,
            VendorTransactionErrorCode::AMOUNT_REQUIRED,
            VendorTransactionErrorCode::AMOUNT_NOT_NUMERIC,
            VendorTransactionErrorCode::AMOUNT_NOT_POSITIVE,
            VendorTransactionErrorCode::STATUS_REQUIRED,
            VendorTransactionErrorCode::INVALID_STATUS_TRANSITION,
            VendorTransactionErrorCode::MALFORMED_JSON => $message,
            default => str_starts_with($message, 'invalid_status_transition:')
                ? VendorTransactionErrorCode::INVALID_STATUS_TRANSITION
                : 'transaction_validation_error',
        };
    }
}
