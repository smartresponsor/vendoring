<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorPayoutEntity> */
final class VendorPayoutRepository extends ServiceEntityRepository implements VendorPayoutRepositoryInterface
{
    private ?EntityManagerInterface $directEm = null;

    /**
     * Accepts either a ManagerRegistry (Symfony DI path) or an EntityManagerInterface
     * directly (unit test path without a full DI container).
     */
    public function __construct(ManagerRegistry|EntityManagerInterface $registry)
    {
        if ($registry instanceof EntityManagerInterface) {
            $this->directEm = $registry;

            return;
        }

        parent::__construct($registry, VendorPayoutEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorPayoutEntity
    {
        if (null !== $this->directEm) {
            $entity = $this->directEm->find(VendorPayoutEntity::class, $id);

            return $entity instanceof VendorPayoutEntity ? $entity : null;
        }

        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorPayoutEntity ? $entity : null;
    }

    private function em(): EntityManagerInterface
    {
        return $this->directEm ?? $this->getEntityManager();
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorPayoutEntity
    {
        if (null !== $this->directEm) {
            $entity = $this->directEm->getRepository(VendorPayoutEntity::class)->findOneBy($criteria, $orderBy);

            return $entity instanceof VendorPayoutEntity ? $entity : null;
        }

        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorPayoutEntity ? $entity : null;
    }

    public function save(VendorPayoutEntity $entity, bool $flush = false): void
    {
        $this->em()->persist($entity);
        if ($flush) {
            $this->em()->flush();
        }
    }

    public function byId(mixed $id): ?VendorPayoutEntity
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

    /** @param array<string, mixed> $meta */
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
