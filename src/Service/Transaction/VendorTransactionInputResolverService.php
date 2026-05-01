<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Transaction;

use App\Vendoring\ServiceInterface\Transaction\VendorTransactionInputResolverServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionInputResolverService implements VendorTransactionInputResolverServiceInterface
{
    public function resolveCreateData(Request $request): VendorTransactionDataValueObject
    {
        $payload = $request->toArray();

        foreach ([
            'vendorId' => VendorTransactionErrorCodeValueObject::VENDOR_ID_REQUIRED,
            'orderId' => VendorTransactionErrorCodeValueObject::ORDER_ID_REQUIRED,
            'amount' => VendorTransactionErrorCodeValueObject::AMOUNT_REQUIRED,
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

        return new VendorTransactionDataValueObject(
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
            throw new InvalidArgumentException(VendorTransactionErrorCodeValueObject::STATUS_REQUIRED);
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
            VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION,
            VendorTransactionErrorCodeValueObject::VENDOR_ID_REQUIRED,
            VendorTransactionErrorCodeValueObject::ORDER_ID_REQUIRED,
            VendorTransactionErrorCodeValueObject::AMOUNT_REQUIRED,
            VendorTransactionErrorCodeValueObject::AMOUNT_NOT_NUMERIC,
            VendorTransactionErrorCodeValueObject::AMOUNT_NOT_POSITIVE,
            VendorTransactionErrorCodeValueObject::STATUS_REQUIRED,
            VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION,
            VendorTransactionErrorCodeValueObject::MALFORMED_JSON => $message,
            default => str_starts_with($message, 'invalid_status_transition:')
                ? VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION
                : 'transaction_validation_error',
        };
    }
}
