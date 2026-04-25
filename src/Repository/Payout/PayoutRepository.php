<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Payout;

use App\Vendoring\Entity\Payout\Payout;
use App\Vendoring\Entity\Payout\PayoutItem;
use App\Vendoring\RepositoryInterface\Payout\PayoutRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

final readonly class PayoutRepository implements PayoutRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function insert(Payout $payout): void
    {
        $this->entityManager->persist($payout);
        $this->entityManager->flush();
    }

    public function insertItem(PayoutItem $item): void
    {
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function byId(string $id): ?Payout
    {
        $entity = $this->entityManager->find(Payout::class, $id);

        return $entity instanceof Payout ? $entity : null;
    }

    /**
     * @return list<PayoutItem>
     */
    public function items(string $payoutId): array
    {
        /** @var list<PayoutItem> $items */
        $items = $this->entityManager->getRepository(PayoutItem::class)->findBy(['payoutId' => $payoutId]);

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

    private function requirePayout(string $id): Payout
    {
        $payout = $this->byId($id);
        if (!$payout instanceof Payout) {
            throw new RuntimeException(sprintf('Payout "%s" not found.', $id));
        }

        return $payout;
    }
}
