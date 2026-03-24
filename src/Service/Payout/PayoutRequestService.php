<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Vendor\Payout\Payout;
use App\ServiceInterface\Payout\PayoutRequestServiceInterface;

final class PayoutRequestService implements PayoutRequestServiceInterface
{
    public function toCreateDto(array $payload): CreatePayoutDTO
    {
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new \InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new CreatePayoutDTO(
            (string) $payload['vendorId'],
            (string) $payload['currency'],
            (int) $payload['thresholdCents'],
            (float) $payload['retentionFeePercent'],
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
}
