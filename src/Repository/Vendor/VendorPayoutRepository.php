<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

final readonly class VendorPayoutRepository implements VendorPayoutRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function insert(VendorPayoutEntity $payout): void
    {
        $this->entityManager->persist($payout);
        $this->entityManager->flush();
    }

    public function insertItem(VendorPayoutItemEntity $item): void
    {
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function byId(string $id): ?VendorPayoutEntity
    {
        $entity = $this->entityManager->find(VendorPayoutEntity::class, $id);

        return $entity instanceof VendorPayoutEntity ? $entity : null;
    }

    /**
     * @return list<VendorPayoutItemEntity>
     */
    public function items(string $payoutId): array
    {
        /** @var list<VendorPayoutItemEntity> $items */
        $items = $this->entityManager->getRepository(VendorPayoutItemEntity::class)->findBy(['payoutId' => $payoutId]);

        return $items;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->requirePayout($id);
        $payout->status = 'processed';
        $payout->processedAt = $processedAt;
        $payout->meta = [...$payout->meta, ...$meta];

        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markFailed(string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->requirePayout($id);
        $payout->status = 'failed';
        $payout->processedAt = $processedAt;
        $payout->meta = [...$payout->meta, ...$meta];

        $this->entityManager->flush();
    }

    private function requirePayout(string $id): VendorPayoutEntity
    {
        $payout = $this->byId($id);
        if (!$payout instanceof VendorPayoutEntity) {
            throw new RuntimeException(sprintf('VendorPayoutEntity "%s" not found.', $id));
        }

        return $payout;
    }
}
