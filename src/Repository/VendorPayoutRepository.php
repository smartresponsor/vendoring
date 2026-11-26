<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\RepositoryInterface\VendorPayoutRepositoryInterface;
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

    public function byId(string $tenantId, mixed $id): ?VendorPayoutEntity
    {
        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
        if (!is_scalar($id)) {
            return null;
        }

        $identifier = trim((string) $id);
        if ('' === $identifier) {
            return null;
        }

        return $this->findOneBy(['tenantId' => $tenantId, 'payoutId' => $identifier]);
    }

    /**
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function reserveAtomically(string $tenantId, string $vendorId, string $currency, callable $operation): mixed
    {
        if ('' === trim($tenantId) || '' === trim($vendorId) || '' === trim($currency)) {
            throw new \InvalidArgumentException('Complete payout scope required');
        }
        $em = $this->em();
        $connection = $em->getConnection();
        if ($connection->isTransactionActive()) {
            throw new \LogicException('Payout reservation requires an independent transaction');
        }

        return $em->wrapInTransaction(static function () use ($connection, $tenantId, $vendorId, $currency, $operation): mixed {
            if ($connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
                // A transaction-scoped lock serializes reservations even when there are no existing payout rows.
                $connection->executeStatement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                $connection->executeStatement("SET LOCAL lock_timeout = '10s'");
                $key = json_encode([$tenantId, $vendorId, $currency], JSON_THROW_ON_ERROR);
                $connection->executeQuery('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [$key])->free();
            } elseif (!$connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SQLitePlatform) {
                throw new \LogicException('Unsupported payout reservation database');
            }

            return $operation();
        });
    }

    public function insert(VendorPayoutEntity $payout): void
    {
        $this->em()->persist($payout);
    }

    public function markProcessed(string $tenantId, string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->byId($tenantId, $id);
        if ($payout instanceof VendorPayoutEntity) {
            $payout->status = 'processed';
            $payout->pendingSlot = null;
            $payout->processedAt = $processedAt;
            $payout->meta = array_merge($payout->meta, $meta);
            $this->em()->flush();
        }
    }

    /** @param array<string, mixed> $meta */
    public function markFailed(string $tenantId, string $id, string $processedAt, array $meta = []): void
    {
        $payout = $this->byId($tenantId, $id);
        if ($payout instanceof VendorPayoutEntity) {
            $payout->status = 'failed';
            $payout->pendingSlot = null;
            $payout->processedAt = $processedAt;
            $payout->meta = array_merge($payout->meta, $meta);
            $this->em()->flush();
        }
    }
}
