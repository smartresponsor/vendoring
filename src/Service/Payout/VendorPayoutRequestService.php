<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Payout;

use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use InvalidArgumentException;

final class VendorPayoutRequestService implements VendorPayoutRequestServiceInterface
{
    /** @param array<string, mixed> $payload */
    public function toCreateDto(array $payload): VendorCreatePayoutDTO
    {
        foreach (['tenantId', 'vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new VendorCreatePayoutDTO(
            vendorId: $this->requiredString($payload, 'vendorId'),
            currency: $this->requiredString($payload, 'currency'),
            thresholdCents: $this->requiredThresholdCents($payload),
            retentionFeePercent: $this->requiredRetentionFeePercent($payload),
            tenantId: $this->requiredString($payload, 'tenantId'),
        );
    }

    public function normalizePayout(VendorPayoutEntity $payout): array
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
            'meta' => $payout->meta,
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
    private function requiredThresholdCents(array $payload): int
    {
        $value = $payload['thresholdCents'] ?? null;

        if (is_int($value)) {
            return $value;
        }

        $numericCandidate = $this->normalizedNumericCandidate($value);

        if (is_numeric($numericCandidate)) {
            return (int) $numericCandidate;
        }

        throw new InvalidArgumentException('thresholdCents required');
    }

    /** @param array<string, mixed> $payload */
    private function requiredRetentionFeePercent(array $payload): float
    {
        $value = $payload['retentionFeePercent'] ?? null;
        $parsed = null;

        if (is_float($value) || is_int($value)) {
            $parsed = (float) $value;
        }

        $numericCandidate = $this->normalizedNumericCandidate($value);

        if (is_numeric($numericCandidate)) {
            $parsed = (float) $numericCandidate;
        }

        if (null === $parsed) {
            throw new InvalidArgumentException('retentionFeePercent required');
        }

        if ($parsed < 0.0 || $parsed > 1.0) {
            throw new InvalidArgumentException('retentionFeePercent out_of_range');
        }

        return $parsed;
    }

    private function normalizedNumericCandidate(mixed $value): string|int|float|null
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return '' === $trimmed ? null : $trimmed;
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return null;
    }
}
