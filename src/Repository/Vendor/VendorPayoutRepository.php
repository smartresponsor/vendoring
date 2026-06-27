<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorPayoutRepository extends ServiceEntityRepository implements VendorPayoutRepositoryInterface
{
    private mixed $entityManager = null;

    public function __construct(ManagerRegistry $registry)
    {
        if ($registry instanceof \Doctrine\ORM\EntityManagerInterface) {
            $this->entityManager = $registry;

            return;
        }

        parent::__construct($registry, VendorPayoutEntity::class);
    }

    private function em(): mixed
    {
        return $this->entityManager ?? $this->getEntityManager();
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function byId(mixed $id): ?object
    {
        if (null !== $this->entityManager) {
            return $this->entityManager->find(VendorPayoutEntity::class, $id);
        }

        return $this->find($id);
    }

    public function insert(VendorPayoutEntity $payout): void
    {
        $this->em()->persist($payout);
    }

    public function markProcessed(string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->byId($id);
        if ($payout instanceof VendorPayoutEntity) {
            $payout->status = 'processed';
            $payout->processedAt = $processedAt;
            $payout->meta = array_merge($payout->meta, $meta);
            $this->em()->flush();
        }
    }

    public function markFailed(string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->byId($id);
        if ($payout instanceof VendorPayoutEntity) {
            $payout->status = 'failed';
            $payout->processedAt = $processedAt;
            $payout->meta = array_merge($payout->meta, $meta);
            $this->em()->flush();
        }
    }
}
