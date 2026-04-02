<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Payout;

use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutItem;

interface PayoutRepositoryInterface
{
    public function insert(Payout $payout): void;

    public function insertItem(PayoutItem $item): void;

    public function byId(string $id): ?Payout;

    /**
     * @return list<PayoutItem>
     */
    public function items(string $payoutId): array;

    /**
     * @param array<string, mixed> $meta
     */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void;

    /**
     * @param array<string, mixed> $meta
     */
    public function markFailed(string $id, string $processedAt, array $meta = []): void;
}
