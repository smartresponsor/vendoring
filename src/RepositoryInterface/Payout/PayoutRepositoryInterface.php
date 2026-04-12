<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Payout;

use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutItem;
use Doctrine\DBAL\Exception;
use JsonException;

interface PayoutRepositoryInterface
{
    /** @throws Exception|JsonException */
    public function insert(Payout $payout): void;

    /** @throws Exception */
    public function insertItem(PayoutItem $item): void;

    /** @throws Exception */
    public function byId(string $id): ?Payout;

    /**
     * @return list<PayoutItem>
     */
    /**
     * @return list<PayoutItem>
     * @throws Exception
     */
    public function items(string $payoutId): array;

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void;

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     */
    public function markFailed(string $id, string $processedAt, array $meta = []): void;
}
