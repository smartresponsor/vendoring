<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\VendorTransactionInputResolverServiceInterface;
use App\ValueObject\VendorTransactionData;
use App\ValueObject\VendorTransactionErrorCode;
use Symfony\Component\HttpFoundation\Request;

/**
 * Application service for vendor transaction input resolver operations.
 */
final class VendorTransactionInputResolverService implements VendorTransactionInputResolverServiceInterface
{
    /**
     * Resolves the requested runtime subject.
     */
    public function resolveCreateData(Request $request): VendorTransactionData
    {
        $payload = $request->toArray();

        foreach ([
            'vendorId' => VendorTransactionErrorCode::VENDOR_ID_REQUIRED,
            'orderId' => VendorTransactionErrorCode::ORDER_ID_REQUIRED,
            'amount' => VendorTransactionErrorCode::AMOUNT_REQUIRED,
        ] as $field => $errorCode) {
            $value = $payload[$field] ?? null;
            $normalized = null === $value ? '' : trim((string) $value);

            if ('' === $normalized) {
                throw new \InvalidArgumentException($errorCode);
            }
        }

        $projectId = null;
        if (array_key_exists('projectId', $payload) && null !== $payload['projectId']) {
            $normalizedProjectId = trim((string) $payload['projectId']);
            $projectId = '' === $normalizedProjectId ? null : $normalizedProjectId;
        }

        return new VendorTransactionData(
            vendorId: trim((string) $payload['vendorId']),
            orderId: trim((string) $payload['orderId']),
            projectId: $projectId,
            amount: (string) $payload['amount'],
        );
    }

    /**
     * Resolves the requested runtime subject.
     */
    public function resolveStatus(Request $request): string
    {
        $payload = $request->toArray();
        $status = isset($payload['status']) ? trim((string) $payload['status']) : '';

        if ('' === $status) {
            throw new \InvalidArgumentException(VendorTransactionErrorCode::STATUS_REQUIRED);
        }

        return $status;
    }

    /**
     * Normalizes the supplied value set for downstream use.
     */
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
