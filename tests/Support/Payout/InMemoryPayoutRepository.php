<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use App\Vendoring\RepositoryInterface\VendorPayoutRepositoryInterface;

final class InMemoryPayoutRepository implements VendorPayoutRepositoryInterface
{
    /** @var array<string,VendorPayoutEntity> */
    private array $payouts = [];

    /** @var array<string,list<VendorPayoutItemEntity>> */
    private array $items = [];

    public function insert(VendorPayoutEntity $payout): void
    {
        $this->payouts[$payout->payoutId] = $payout;
    }

    public function insertItem(VendorPayoutItemEntity $item): void
    {
        $payoutId = $item->payout->payoutId;
        $this->items[$payoutId] ??= [];
        $this->items[$payoutId][] = $item;
    }

    public function find(mixed $id): ?VendorPayoutEntity
    {
        if (!is_scalar($id)) {
            return null;
        }

        return $this->payouts[(string) $id] ?? null;
    }

    public function findOneBy(array $criteria): ?VendorPayoutEntity
    {
        return $this->findBy($criteria)[0] ?? null;
    }

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorPayoutEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return array_values(array_filter(
            $this->payouts,
            static function (VendorPayoutEntity $payout) use ($criteria): bool {
                foreach ($criteria as $field => $value) {
                    if (!property_exists($payout, $field) || $payout->{$field} !== $value) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }

    public function save(VendorPayoutEntity $entity, bool $flush = false): void
    {
        $this->insert($entity);
    }

    public function byId(string $tenantId, mixed $id): ?VendorPayoutEntity
    {
        if (!is_scalar($id)) {
            return null;
        }

        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
        $payout = $this->payouts[(string) $id] ?? null;

        return null !== $payout && $payout->tenantId === $tenantId ? $payout : null;
    }

    /** @return list<VendorPayoutItemEntity> */
    public function items(string $payoutId): array
    {
        return $this->items[$payoutId] ?? [];
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markProcessed(string $tenantId, string $id, string $processedAt, array $meta = []): void
    {
        if (null === $this->byId($tenantId, $id)) {
            return;
        }

        $this->payouts[$id]->status = 'processed';
        $this->payouts[$id]->processedAt = $processedAt;
        $this->payouts[$id]->meta = [...$this->payouts[$id]->meta, ...$meta];
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markFailed(string $tenantId, string $id, string $processedAt, array $meta = []): void
    {
        if (null === $this->byId($tenantId, $id)) {
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
