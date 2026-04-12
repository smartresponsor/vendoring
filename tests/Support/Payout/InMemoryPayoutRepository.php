<?php

declare(strict_types=1);

namespace App\Tests\Support\Payout;

use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutItem;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;

final class InMemoryPayoutRepository implements PayoutRepositoryInterface
{
    /** @var array<string,Payout> */
    private array $payouts = [];

    /** @var array<string,list<PayoutItem>> */
    private array $items = [];

    public function insert(Payout $payout): void
    {
        $this->payouts[$payout->id] = $payout;
    }

    public function insertItem(PayoutItem $item): void
    {
        $this->items[$item->payoutId] ??= [];
        $this->items[$item->payoutId][] = $item;
    }

    public function byId(string $id): ?Payout
    {
        return $this->payouts[$id] ?? null;
    }

    public function items(string $payoutId): array
    {
        return $this->items[$payoutId] ?? [];
    }

    public function markProcessed(string $id, string $processedAt, array $meta = []): void
    {
        if (!isset($this->payouts[$id])) {
            return;
        }

        $this->payouts[$id]->status = 'processed';
        $this->payouts[$id]->processedAt = $processedAt;
        $this->payouts[$id]->meta = [...$this->payouts[$id]->meta, ...$meta];
    }

    public function markFailed(string $id, string $processedAt, array $meta = []): void
    {
        if (!isset($this->payouts[$id])) {
            return;
        }

        $this->payouts[$id]->status = 'failed';
        $this->payouts[$id]->processedAt = $processedAt;
        $this->payouts[$id]->meta = [...$this->payouts[$id]->meta, ...$meta];
    }

    /** @return list<Payout> */
    public function all(): array
    {
        return array_values($this->payouts);
    }
}
