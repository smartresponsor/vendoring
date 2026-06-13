<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;

final class InMemoryPayoutRepository implements VendorPayoutRepositoryInterface
{
    /** @var array<string,VendorPayoutEntity> */
    private array $payouts = [];

    /** @var array<string,list<VendorPayoutItemEntity>> */
    private array $items = [];

    public function insert(VendorPayoutEntity $payout): void
    {
        $this->payouts[$payout->id] = $payout;
    }

    public function insertItem(VendorPayoutItemEntity $item): void
    {
        $this->items[$item->payoutId] ??= [];
        $this->items[$item->payoutId][] = $item;
    }

    public function byId(string $id): ?VendorPayoutEntity
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

    /** @return list<VendorPayoutEntity> */
    public function all(): array
    {
        return array_values($this->payouts);
    }
}
