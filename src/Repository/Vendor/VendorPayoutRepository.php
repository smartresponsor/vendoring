<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class VendorPayoutRepository extends ServiceEntityRepository implements VendorPayoutRepositoryInterface
{
    private ?EntityManagerInterface $directEm = null;

    /**
     * Accepts either a ManagerRegistry (Symfony DI path) or an EntityManagerInterface
     * directly (unit test path without a full DI container).
     */
    public function __construct(mixed $registry)
    {
        if ($registry instanceof EntityManagerInterface) {
            $this->directEm = $registry;

            return;
        }

        /* @var ManagerRegistry $registry */
        parent::__construct($registry, VendorPayoutEntity::class);
    }

    private function em(): EntityManagerInterface
    {
        return $this->directEm ?? $this->getEntityManager();
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->em()->persist($entity);
        if ($flush) {
            $this->em()->flush();
        }
    }

    public function byId(mixed $id): ?object
    {
        if (null !== $this->directEm) {
            return $this->directEm->find(VendorPayoutEntity::class, $id);
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
