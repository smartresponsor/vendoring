<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Payout;

use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutItem;

/**
 * Persistence contract for payout repository records.
 */
interface PayoutRepositoryInterface
{
    /**
     * Executes the insert operation for this runtime surface.
     */
    public function insert(Payout $payout): void;

    /**
     * Executes the insert item operation for this runtime surface.
     */
    public function insertItem(PayoutItem $item): void;

    /**
     * Executes the by id operation for this runtime surface.
     */
    public function byId(string $id): ?Payout;

    /**
     * @return list<PayoutItem>
     */
    public function items(string $payoutId): array;

    /**
     * Executes the mark processed operation for this runtime surface.
     */
    public function markProcessed(string $id, string $processedAt): void;
}
