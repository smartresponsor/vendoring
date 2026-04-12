<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
        foreach (['tenantId', 'vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new CreatePayoutDTO(
            vendorId: $this->requiredString($payload, 'vendorId'),
            currency: $this->requiredString($payload, 'currency'),
            thresholdCents: $this->requiredThresholdCents($payload),
            retentionFeePercent: $this->requiredRetentionFeePercent($payload),
            tenantId: $this->requiredString($payload, 'tenantId'),
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

        if (is_numeric($value)) {
            return (int) $value;
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

        if (is_numeric($value)) {
            $parsed = (float) $value;
        }

        if (null === $parsed) {
            throw new InvalidArgumentException('retentionFeePercent required');
        }

        if ($parsed < 0.0 || $parsed > 1.0) {
            throw new InvalidArgumentException('retentionFeePercent out_of_range');
        }

        return $parsed;
    }
}
