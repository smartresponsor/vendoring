<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Payout\Payout;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use InvalidArgumentException;

final class VendorPayoutRequestService implements VendorPayoutRequestServiceInterface
{
    /** @param array<string, mixed> $payload */
    public function toCreateDto(array $payload): CreatePayoutDTO
    {
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new CreatePayoutDTO(
            $this->requiredString($payload, 'vendorId'),
            $this->requiredString($payload, 'currency'),
            $this->requiredInt($payload, 'thresholdCents'),
            $this->requiredFloat($payload, 'retentionFeePercent'),
        );
    }

    public function normalizePayout(Payout $payout): array
    {
        return [
            'id' => $payout->id,
            'vendorId' => $payout->vendorId,
            'currency' => $payout->currency,
            'grossCents' => $payout->grossCents,
            'feeCents' => $payout->feeCents,
            'netCents' => $payout->netCents,
            'status' => $payout->status,
            'createdAt' => $payout->createdAt,
            'processedAt' => $payout->processedAt,
        ];
    }

    /** @param array<string, mixed> $payload */
    private function requiredString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        throw new InvalidArgumentException(sprintf('%s required', $field));
    }

    /** @param array<string, mixed> $payload */
    private function requiredInt(array $payload, string $field): int
    {
        $value = $payload[$field] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        throw new InvalidArgumentException(sprintf('%s required', $field));
    }

    /** @param array<string, mixed> $payload */
    private function requiredFloat(array $payload, string $field): float
    {
        $value = $payload[$field] ?? null;

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        throw new InvalidArgumentException(sprintf('%s required', $field));
    }
}
